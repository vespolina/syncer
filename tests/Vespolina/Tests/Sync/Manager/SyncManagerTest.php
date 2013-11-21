<?php

/**
 * (c) 2011 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\Tests\Sync\Entity;

use Monolog\Logger;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Vespolina\Sync\Manager\SyncManager;
use Vespolina\Sync\Gateway\SyncMemoryGateway;

class SyncManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $dispatcher = new EventDispatcher();
        $gateway = new SyncMemoryGateway();
        $logger = new Logger('test');
        $config = array();

        $manager = new SyncManager($gateway, $dispatcher, $logger, $config);

        $this->assertNotNull($manager);
    }

}
