<?php

/**
 * (c) 2011 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\Tests\Sync\Gateway;

use Vespolina\Sync\Gateway\SyncMemoryGateway;

class SyncMemoryGatewayTest extends \PHPUnit_Framework_TestCase
{
    /* @var $gateway Vespolina\Sync\Gateway\SyncMemoryGateway */
    protected $gateway;

    protected function setUp()
    {
        $this->gateway = new SyncMemoryGateway();
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
}