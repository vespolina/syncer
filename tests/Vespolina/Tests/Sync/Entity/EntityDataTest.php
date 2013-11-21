<?php

/**
 * (c) 2011 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\Tests\Sync\Entity;

use Vespolina\Sync\Entity\EntityData;

class EntityDataTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $entityData = new EntityData('book', '12345', '<xml>...</xml>');

        $this->assertEquals('book', $entityData->getEntityName());
        $this->assertEquals('12345', $entityData->getEntityId());
        $this->assertEquals('<xml>...</xml>', $entityData->getData());
    }

    public function testAddResolvedDependency()
    {
        $entityData = new EntityData('book', '12345', '<xml>...</xml>');
        $entityData->addDependency('author', '9876', 'referencingObject');

        // Tests if the dependency is existent
        foreach ($entityData->getDependencies() as $entityName => $dependency) {
            $this->assertEquals('author', $entityName);
            $this->assertEquals('9876', $dependency['data']);
            $this->assertEquals('referencingObject', $dependency['reference']);
        };
    }

    public function testAddUnresolvedDependency()
    {
        $entityData = new EntityData('book', '12345', '<xml>...</xml>');
        $entityData->addDependency('author', '9876');

        // Tests if the dependency is existent
        foreach ($entityData->getUnresolvedDependencies() as $entityName => $dependency) {
            $this->assertEquals('author', $entityName);
            $this->assertEquals('9876', $dependency['data']);
            $this->assertNull($dependency['reference']);
        };
    }

    public function testGetKey()
    {
        $entityData = new EntityData('book', '12345', '<xml>...</xml>');
        $this->assertEquals('book.12345', $entityData->getKey());
    }
}