<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Form\DataTransformer;

use Sonata\AdminBundle\Form\DataTransformer\ModelToIdPropertyTransformer;
use Sonata\AdminBundle\Tests\Fixtures\Entity\Foo;
use Doctrine\Common\Collections\ArrayCollection;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;

class ModelToIdPropertyTransformerTest extends \PHPUnit_Framework_TestCase
{
    private $modelManager = null;

    public function setUp()
    {
        $this->modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');
    }

    public function testReverseTransform()
    {
        $transformer = new ModelToIdPropertyTransformer($this->modelManager, 'Sonata\AdminBundle\Tests\Fixtures\Entity\Foo', 'bar', false);

        $entity = new Foo();
        $entity->setBar('example');

        $this->modelManager
                ->expects($this->any())
                ->method('find')
                ->with($this->equalTo('Sonata\AdminBundle\Tests\Fixtures\Entity\Foo'), $this->equalTo(123))
                ->will($this->returnValue($entity));

        $this->assertNull($transformer->reverseTransform(null));
        $this->assertNull($transformer->reverseTransform(false));
        $this->assertNull($transformer->reverseTransform(12));
        $this->assertEquals($entity, $transformer->reverseTransform(array('identifiers'=>array(123), 'titles'=>array('example'))));
    }

    public function testTransform()
    {
        $entity = new Foo();
        $entity->setBar('example');

        $this->modelManager->expects($this->once())
            ->method('getIdentifierValues')
            ->will($this->returnValue(array(123)));

        $transformer = new ModelToIdPropertyTransformer($this->modelManager, 'Sonata\AdminBundle\Tests\Fixtures\Entity\Foo', 'bar', false);

        $this->assertEquals(array('identifiers' => array(), 'titles' => array()), $transformer->transform(null));
        $this->assertEquals(array('identifiers' => array(), 'titles' => array()), $transformer->transform(false));
        $this->assertEquals(array('identifiers' => array(), 'titles' => array()), $transformer->transform(0));
        $this->assertEquals(array('identifiers' => array(), 'titles' => array()), $transformer->transform('0'));

        $this->assertEquals(array('identifiers'=>array(123), 'titles'=>array('example')), $transformer->transform($entity));
    }

    public function testTransformMultiple()
    {
        $entity1 = new Foo();
        $entity1->setBar('foo');

        $entity2 = new Foo();
        $entity2->setBar('bar');

        $entity3 = new Foo();
        $entity3->setBar('baz');

        $collection = new ArrayCollection();
        $collection[] = $entity1;
        $collection[] = $entity2;
        $collection[] = $entity3;

        $this->modelManager->expects($this->exactly(3))
            ->method('getIdentifierValues')
            ->will($this->returnCallback(function($value) use ($entity1, $entity2, $entity3) {
                if ($value == $entity1) {
                    return array(123);
                }

                if ($value == $entity2) {
                    return array(456);
                }

                if ($value == $entity3) {
                    return array(789);
                }

                return array(999);
            }));

        $transformer = new ModelToIdPropertyTransformer($this->modelManager, 'Sonata\AdminBundle\Tests\Fixtures\Entity\Foo', 'bar', true);

        $this->assertEquals(array('identifiers' => array(), 'titles' => array()), $transformer->transform(null));
        $this->assertEquals(array('identifiers' => array(), 'titles' => array()), $transformer->transform(false));
        $this->assertEquals(array('identifiers' => array(), 'titles' => array()), $transformer->transform(0));
        $this->assertEquals(array('identifiers' => array(), 'titles' => array()), $transformer->transform('0'));

        $this->assertEquals(array('identifiers'=>array(123, 456, 789), 'titles'=>array('foo', 'bar', 'baz')), $transformer->transform($collection));
    }
}
