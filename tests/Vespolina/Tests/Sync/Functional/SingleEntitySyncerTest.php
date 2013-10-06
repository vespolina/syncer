<?php

/**
 * (c) 2011 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
 
namespace Vespolina\Tests\Functional;

use Monolog\Logger;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\EventDispatcher\EventDispatcher;

use Vespolina\Sync\Entity\EntityData;
use Vespolina\Sync\Entity\SyncState;
use Vespolina\Sync\Gateway\SyncMemoryGateway;
use Vespolina\Sync\ServiceAdapter\AbstractServiceAdapter;
use Vespolina\Sync\Manager\SyncManager;

/**
 */
class SingleEntitySyncerTest extends \PHPUnit_Framework_TestCase
{
    protected $manager;
    protected $dispatcher;
    protected $gateway;
    protected $remoteServiceAdapter;

    protected function setUp()
    {
        $this->dispatcher = new EventDispatcher();
        $this->gateway = new SyncMemoryGateway();

        $logger = new Logger('test');

        $yamlParser = new Parser();
        $config = $yamlParser->parse(file_get_contents(__DIR__ . '/single_entity.yml'));
        $this->manager = new SyncManager($this->gateway, $this->dispatcher, $logger, $config['syncer']);
    }

    public function testSyncEntitiesFromOneRemoteService()
    {
        //Setup a remote service with some entities we want to locally sync
        $this->setupRemoteService();

        //Setup (fake) the synchronization state
        $this->setSyncState();

        //Perform synchronization
        $this->manager->execute(array('product'));


        //Test if all requested entities have been synced
        $state = $this->manager->getState('product');

        $this->assertEquals($state->getLastValue(), 20);

    }
    protected function setSyncState()
    {
        $syncState = new SyncState('product');

        //Fake that we already  synced 5 entities last time
        $syncState->setLastValue(5);

        $this->manager->updateState($syncState);
    }

    protected function setupRemoteService()
    {
        //Create a remote service adapter which can deal with products
        $this->remoteServiceAdapter = new DummyRemoteServiceAdapter(array('product'));

        for ($i = 1; $i <= 20;$i++) {
            $entity = new RemoteProduct();
            $entity->id = $i;
            $this->remoteServiceAdapter->add($entity);
        }

        $this->manager->addServiceAdapter($this->remoteServiceAdapter);
    }
}


class LocalProduct{
    public $id;
    public function getId() {
        return $this->id;
    }
}
class RemoteProduct{
    public $id;
}

/**
 * A dummy remote service adapter, eg. in the real world it might be an adaptor to web services
 * such as Zoho, Magento Go, ...
 *
 * Class DummyRemoteServiceAdapter
 * @package Vespolina\Tests\Functional
 */
class DummyRemoteServiceAdapter extends AbstractServiceAdapter
{
    protected $entities;
    protected $size;
    protected $lastValue;

    public function add($entity)
    {
        if (null == $this->entities) $this->entities = array();
        $this->entities[$entity->id] = $entity;
    }

    public function fetchEntity($entityName, $remoteId)
    {
        if (array_key_exists($remoteId, $this->entities)) {

            return new EntityData($entityName, $remoteId, '<xml>...blablabla...</xml>');
        }
    }

    public function fetchEntities($entityName, $lastValue, $size)
    {
        $out = array();

        //Simple naive implementation comparing the entity id
        foreach ($this->entities as $entity) {

            if ($entity->id > $lastValue || null == $lastValue) {
                $out[] = new EntityData($entityName, $entity->id);
            }
        }

       return $out;
    }

    public function transformEntityData(EntityData $entityData)
    {
        $product = new LocalProduct();
        $product->id = 'local' . $entityData->getEntityId();   //In reality the local persistence gateway would generate local id

        return $product;
    }
}