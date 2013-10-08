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

/**
 */
class SyncEntitiesWithDependencyTest extends \PHPUnit_Framework_TestCase
{
    protected $dispatcher;
    protected $gateway;
    protected $logHandler;
    protected $manager;
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

        //Create a remote service adapter which can deal with products
        $this->remoteServiceAdapter = new SimpleRemoteServiceAdapter(array('category', 'product'));
        $this->remoteServiceAdapter->setupFakeData();

        $this->manager->addServiceAdapter($this->remoteServiceAdapter);
    }

    public function testSyncEntities()
    {
        $state = $this->manager->getState('product');
        $this->assertNull($state->getLastValue());

        // Perform synchronization
        $this->manager->execute(array('product'));

        // Verify that the log does not contain any issues
        $this->assertFalse($this->logHandler->hasErrorRecords(), 'Sync should not have any errors');
        // Test if all requested entities have been synced
        $state = $this->manager->getState('product');

        $this->assertEquals($state->getLastValue(), 20);
    }
}