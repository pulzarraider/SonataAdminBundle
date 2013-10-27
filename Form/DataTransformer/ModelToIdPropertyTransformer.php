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

    /**
     * @param ModelManagerInterface $modelManager
     * @param string                $className
     * @param string                $property
     */
    public function __construct(ModelManagerInterface $modelManager, $className, $property)
    {
        $this->modelManager = $modelManager;
        $this->className    = $className;
        $this->property     = $property;
    }

    /**
     * {@inheritDoc}
     */
    public function reverseTransform($value)
    {
        if (empty($value) || !isset($value['identifier']) || !isset($value['title'])) {
            return null;
        }

        return $this->modelManager->find($this->className, $value['identifier']);
    }

    /**
     * {@inheritDoc}
     */
    public function transform($entity)
    {
        if (empty($entity)) {
            return null;
        }

        $id = current($this->modelManager->getIdentifierValues($entity));
        $title = call_user_func(array($entity, 'get'.ucfirst($this->property)));

        return array('identifier'=>$id, 'title'=>$title);
    }
}
