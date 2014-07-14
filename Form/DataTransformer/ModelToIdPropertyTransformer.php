<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Sonata\AdminBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use RuntimeException;

/**
 * Transform object to ID and property title
 *
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class ModelToIdPropertyTransformer implements DataTransformerInterface
{
    protected $modelManager;

    protected $className;

    protected $property;

    protected $multiple;

    /**
     * @param ModelManagerInterface $modelManager
     * @param string                $className
     * @param string                $property
     */
    public function __construct(ModelManagerInterface $modelManager, $className, $property, $multiple)
    {
        $this->modelManager = $modelManager;
        $this->className    = $className;
        $this->property     = $property;
        $this->multiple     = $multiple;
    }

    /**
     * {@inheritDoc}
     */
    public function reverseTransform($value)
    {
        $collection = $this->modelManager->getModelCollectionInstance($this->className);

        if (empty($value) || empty($value['identifiers'])) {
            if (!$this->multiple) {
                return null;
            } else {
                return $collection;
            }
        }

        if (!$this->multiple) {
             return $this->modelManager->find($this->className, current($value['identifiers']));
        }

        $identifierFieldName = current($this->modelManager->getIdentifierFieldNames($this->className));
        $queryBuilder = $this->modelManager->createQuery($this->className, 'o');

        $idx        = array();
        $connection = $queryBuilder->getEntityManager()->getConnection();
        foreach ($value['identifiers'] as $id) {
            $idx[] = $connection->quote($id);
        }
        $queryBuilder->andWhere(sprintf('o.%s IN (%s)', $identifierFieldName, implode(',', $idx)));
        $query = $queryBuilder->getQuery();

        foreach ($query->getResult() as $entity) {
            $collection->add($entity);
        }

        return $collection;
    }

    /**
     * {@inheritDoc}
     */
    public function transform($entityOrCollection)
    {
        $result = array('identifiers' => array(), 'titles' => array());

        if (!$entityOrCollection) {
            return $result;
        }
        if ($entityOrCollection instanceof \ArrayAccess) {
            $collection = $entityOrCollection;
        } else {
            $collection = array($entityOrCollection);
        }

        if (!$this->property) {
            throw new RuntimeException('Please define "property" parameter.');
        }

        foreach ($collection as $entity) {
            $id  = current($this->modelManager->getIdentifierValues($entity));
            $title = call_user_func(array($entity, 'get'.ucfirst($this->property)));

            $result['identifiers'][] = $id;
            $result['titles'][] = $title;
        }

        return $result;
    }
}
