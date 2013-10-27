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

class ModelToIdPropertyTransformerTest extends \PHPUnit_Framework_TestCase
{
    private $modelManager = null;

    public function setUp()
    {
        $this->modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');
    }

    public function testReverseTransformWhenPassing0AsId()
    {
        $transformer = new ModelToIdPropertyTransformer($this->modelManager, 'Foo', 'bar');

        $entity = new Foo();
        $entity->setBar('example');

        $this->modelManager
                ->expects($this->any())
                ->method('find')
                ->with($this->equalTo('Foo'), $this->equalTo(123))
                ->will($this->returnValue($entity));

        $this->assertNull($transformer->reverseTransform(null));
        $this->assertNull($transformer->reverseTransform(false));
        $this->assertNull($transformer->reverseTransform(12));
        $this->assertEquals($entity, $transformer->reverseTransform(array('identifier'=>123, 'title'=>'example')));
    }

    public function testTransform()
    {
        $this->modelManager->expects($this->once())
            ->method('getIdentifierValues')
            ->will($this->returnValue(array(123)));

        $transformer = new ModelToIdPropertyTransformer($this->modelManager, 'Foo', 'bar');

        $this->assertNull($transformer->transform(null));
        $this->assertNull($transformer->transform(false));
        $this->assertNull($transformer->transform(0));
        $this->assertNull($transformer->transform('0'));

        $entity = new Foo();
        $entity->setBar('example');

        $this->assertEquals(array('identifier'=>123, 'title'=>'example'), $transformer->transform($entity));
    }
}
