<?php

/**
 * (c) 2011 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\Tests\Sync\Gateway;

use Vespolina\Sync\Entity\SyncState;

abstract class SyncGatewayTestCommon extends \PHPUnit_Framework_TestCase
{
    /* @var $gateway \Vespolina\Sync\Gateway\SyncMemoryGateway */
    protected $gateway;

    public function setUp()
    {
        // some common logic to sync gateway implementations
    }

    public function testUpdateIdMapping()
    {
        $this->gateway->updateIdMapping('book', 'Local-1234', 'Amazone-567');
        $this->assertEquals('Local-1234', $this->gateway->findLocalId('book', 'Amazone-567'));
    }

    public function testFindUnknownLocalId()
    {
        $this->assertEquals(null, $this->gateway->findLocalId('bookblablbla', 'Amazone-000'));
    }

    public function testFindAndUpdateState()
    {
        // This is a new entity collection
        $state = new SyncState('book');
        $state->setLastValue(42);
        $this->gateway->updateState($state);

        $newState = $this->gateway->findStateByEntityName('book');
        $this->assertEquals(42, $newState->getLastValue());

        $newState->setLastValue(777);
        $this->gateway->updateState($newState);

        $newestState = $this->gateway->findStateByEntityName('book');
        $this->assertEquals(777, $newestState->getLastValue());
    }
}
