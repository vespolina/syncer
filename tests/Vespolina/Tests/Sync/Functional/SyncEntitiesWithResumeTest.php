<?php

/**
 * (c) 2011 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\Tests\Sync\Functional;

use Monolog\Logger;
use Monolog\Handler\TestHandler;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Vespolina\Sync\Gateway\SyncMemoryGateway;
use Vespolina\Sync\Manager\SyncManager;

class SyncEntitiesWithResumeTest extends \PHPUnit_Framework_TestCase
{
    protected $logHandler;
    protected $manager;
    protected $dispatcher;
    protected $gateway;
    protected $remoteServiceAdapter;

    protected function setUp()
    {
        $this->dispatcher = new EventDispatcher();
        $this->gateway = new SyncMemoryGateway();

        $this->logHandler = new TestHandler();
        $logger = new Logger('test', array($this->logHandler));

        $yamlParser = new Parser();
        $config = $yamlParser->parse(file_get_contents(__DIR__ . '/single_entity.yml'));
        $this->manager = new SyncManager($this->gateway, $this->dispatcher, $logger, $config['syncer']);

        // Create a remote service adapter which can deal with products
        $this->remoteServiceAdapter = new SimpleRemoteServiceAdapter(array('category', 'product'));
        $this->remoteServiceAdapter->setupFakeData();

        $this->manager->addServiceAdapter($this->remoteServiceAdapter);
    }

    public function testSyncEntities()
    {
        // Initially nothing should have yet been synced
        $state = $this->manager->getState('product');

        // Get the last key value, should be initially null
        $this->assertNull($state->getLastValue());

        // SYNC 1 : get the first 2 products
        $this->manager->execute(array('product'), 2);

        $state = $this->manager->getState('product');
        $this->assertEquals(2, $state->getLastValue(), 'only 2 should have been synced');

        // SYNC 2 : get the next six products
        $this->manager->execute(array('product'), 6);
        $this->assertEquals($state->getLastValue(), 8, 'now 6 more, that is 8');

        // SYNC 3 : get the rest of the products
        $this->manager->execute(array('product'), 222222);
        $this->assertEquals($state->getLastValue(), 20, '20 total products synced');

        // Verify that the log does not contain any issues
        $this->assertFalse($this->logHandler->hasErrorRecords(), 'Sync logs should not have any errors');
    }
}
