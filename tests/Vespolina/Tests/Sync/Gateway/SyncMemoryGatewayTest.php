<?php

/**
 * (c) 2011 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\Tests\Sync\Gateway;

use Vespolina\Sync\Entity\EntityData;
use Vespolina\Sync\Gateway\SyncMemoryGateway;

class SyncMemoryGatewayTest extends SyncGatewayTestCommon
{
    protected function setUp()
    {
        $this->gateway = new SyncMemoryGateway();
    }

    public function testUpdateEntityData()
    {
        $ed1 = new EntityData('entity1', '987', 'data');
        $this->gateway->updateEntityData($ed1);

        $refl = new \ReflectionObject($this->gateway);
        $prop = $refl->getProperty('entityData');
        $prop->setAccessible(true);

        $this->assertTrue(array_key_exists($ed1->getKey(), $prop->getValue($this->gateway)));
    }
}