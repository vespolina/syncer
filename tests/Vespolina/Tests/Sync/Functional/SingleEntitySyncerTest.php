<?php

/**
 * (c) 2011 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
 
namespace Vespolina\Tests\Functional;

use Monolog\Logger;
use Monolog\Handler\TestHandler;
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
    }

    public function testSyncEntitiesFromOneRemoteService()
    {
        //Setup a remote service with some entities we want to locally sync
        $this->setupRemoteService();

        //Setup (fake) the synchronization state
        $this->setSyncState();

        //Perform synchronization
        $this->manager->execute(array('product'));

        //Verify that the log does not contain any issues
        $this->assertFalse($this->logHandler->hasErrorRecords(), 'Sync should not have any errors');
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
        $this->remoteServiceAdapter = new DummyRemoteServiceAdapter(array('category', 'product'));

        for ($i = 1; $i <= 20;$i++) {

            //Create a remote product category
            $cat = new RemoteProductCategory();
            $cat->name = 'cat' . $i;
            $this->remoteServiceAdapter->addProductCategory($cat);

            //The primary object we will be syncing
            $remoteProduct = new RemoteProduct();
            $remoteProduct->id = $i;

            //Setup a depending object requiring individual  syncing
            $remoteProduct->category = $cat;

            $this->remoteServiceAdapter->addProduct($remoteProduct);
        }

        $this->manager->addServiceAdapter($this->remoteServiceAdapter);
    }
}

/**
 * Below for both a product and category a local and remote representation
 */

class LocalProduct
{
    public $id;
    public $category;

    public function getId() {
        return $this->id;
    }
}

class RemoteProduct{
    public $id;
    public $category;
}

class LocalProductCategory
{
    public $name;

    public function getId() {
        return $this->name;
    }
}

class RemoteProductCategory
{
    public $name;
}


/**
 * A dummy remote service adapter, eg. in the real world it might be an adaptor to web services
 * such as Zoho, Magento Go, ...
 *
 * This dummy test provider supports the synchronization of remote products and associated product category
 * Class DummyRemoteServiceAdapter
 * @package Vespolina\Tests\Functional
 */
class DummyRemoteServiceAdapter extends AbstractServiceAdapter
{
    protected $remoteProducts;
    protected $remoteCategories;
    protected $size;
    protected $lastValue;

    public function addProduct($remoteProduct)
    {
        if (null == $this->remoteProducts) $this->remoteProducts = array();
        $this->remoteProducts[$remoteProduct->id] = $remoteProduct;
    }

    public function addProductCategory($remoteProductCategory)
    {
        if (null == $this->remoteCategories) $this->remoteCategories = array();
        $this->remoteCategories[$remoteProductCategory->name] = $remoteProductCategory;
    }

    public function fetchEntity($entityName, $remoteId)
    {
        switch($entityName) {
            case 'product':
                if (array_key_exists($remoteId, $this->remoteProducts)) {

                    return new EntityData($entityName, $remoteId, '<xml>...blablabla...</xml>');
                }
                break;
            case 'category':
                if (array_key_exists($remoteId, $this->remoteCategories)) {

                    return new EntityData($entityName, $remoteId, '<xml>...blablabla...</xml>');
                }
                break;
        }
    }

    public function fetchEntities($entityName, $lastValue, $size)
    {
        $out = array();

        switch($entityName) {
            case 'product':
                //Simple naive implementation comparing the entity id
                foreach ($this->remoteProducts as $remoteProduct) {

                    if ($remoteProduct->id > $lastValue || null == $lastValue) {

                        $ed = new EntityData($entityName, $remoteProduct->id);

                        //Indicate to the sync manager that we need the category dependency
                        $ed->addDependency('category', 'cat' . $remoteProduct->id);
                        $out[] = $ed;
                    }
                }
                break;
            case 'category':
                // Even more naive
                foreach ($this->remoteCategories as $remoteCat) {
                        $out[] = new EntityData($entityName, $remoteCat->name);
                }
                break;
        }

       return $out;
    }

    public function transformEntityData(EntityData $entityData)
    {
        switch($entityData->getEntityName()) {
            case 'product':
                $product = new LocalProduct();
                $product->id = 'local' . $entityData->getEntityId();   //In reality the local persistence gateway would generate local id

                $product->category = $entityData->getDependencyReference('category');
                return $product;

            case 'category':
                $cat = new LocalProductCategory();
                $cat->name = $entityData->getEntityId();   //In reality the local persistence gateway would generate local id

                return $cat;
        }
    }
}