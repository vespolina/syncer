<?php

/**
 * (c) 2011 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
 
namespace Vespolina\Sync\Manager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Vespolina\Sync\Entity\SyncState;
use Vespolina\Sync\Gateway\SyncGatewayInterface;
use Vespolina\Sync\Handler\DefaultEntityHandler;
use Vespolina\Sync\ServiceAdapter\ServiceAdapterInterface;

class SyncManager
{
    protected $dispatcher;
    protected $dependencyHandlers;
    protected $gateway;
    protected $serviceAdaptersByEntityName;
    protected $queues;

    public function __construct(SyncGatewayInterface $gateway, EventDispatcherInterface $dispatcher)
    {
        $this->dependencyHandlers = array();
        $this->dispatcher = $dispatcher;
        $this->gateway = $gateway;
        $this->serviceAdaptersByEntityName = array();
        $this->queues = array();

        //Setup the default dependency handler
        $this->dependencyHandlers['default'] = new DefaultEntityHandler();
    }

    public function addServiceAdapter(ServiceAdapterInterface $adapter)
    {
        //Store service adapters per entity name they support
        foreach ($adapter->getSupportedEntities() as $entityName) {
            $this->serviceAdaptersByEntityName[$entityName] = $adapter;
        }
    }

    public function execute(array $entityNames = array())
    {
        foreach ($entityNames as $entityName) {

            //Prepare synchronization, retrieve the last key value we retrieved
            $state = $this->getState($entityName);
            $lastValue = $state->getLastValue();

            //Get the service adapter for this entity
            $adapter = $this->getServiceAdapter($entityName);

            //Fetch entities after 'lastValue'
            $entities = $adapter->fetchEntities($lastValue, 100);

            //Analyze and adjust the entity sync state
            $this->analyseEntities($state, $entities);

            //Persist the updated state of this entity
            $this->updateState($state);
        }
    }

    public function getServiceAdapter($entityName)
    {
        if (array_key_exists($entityName, $this->serviceAdaptersByEntityName)) {

            return $this->serviceAdaptersByEntityName[$entityName];

        } else {
            throw new \RuntimeException('No service adapter available for ' . $entityName);
        }
    }

    public function getState($entityName)
    {
        $state = $this->gateway->findStateByEntityName($entityName);

        if (null == $state) {
            $state = new SyncState($entityName);
        }

        return $state;
    }

    public function updateState(SyncState $state)
    {
        $this->gateway->updateState($state);
    }

    protected function analyseEntities(SyncState $state, array $entities)
    {
        if (count($entities) == 0) return;

        //Get the last entity
        $lastEntity = end($entities);
        $state->setLastValue($lastEntity->id);  //TODO: make generic
    }
}