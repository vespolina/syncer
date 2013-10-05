<?php

/**
 * (c) 2011 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
 
namespace Vespolina\Sync\Manager;
use Monolog\Logger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Vespolina\Sync\Entity\SyncState;
use Vespolina\Sync\Gateway\SyncGatewayInterface;
use Vespolina\Sync\Handler\DefaultEntityHandler;
use Vespolina\Sync\ServiceAdapter\ServiceAdapterInterface;
use Vespolina\Sync\Entity\EntityData;

class SyncManager implements SyncManagerInterface
{
    protected $config;
    protected $dispatcher;
    protected $handlers;
    protected $gateway;
    protected $serviceAdaptersByEntityName;
    protected $queues;

    public function __construct(SyncGatewayInterface $gateway, EventDispatcherInterface $dispatcher, $logger, $config = array())
    {
        $this->dispatcher = $dispatcher;
        $this->handlers = array();
        $this->logger = $logger;
        $this->gateway = $gateway;
        $this->serviceAdaptersByEntityName = array();
        $this->queues = array();

        $defaultConfig = array('delay_dependency_processing' => false,
                               'use_id_mapping' => true);
        $this->config = array_merge($config, $defaultConfig);

        //Setup the default entity handler
        $this->handlers['default'] = new DefaultEntityHandler();
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

            //Fetch raw entity data after 'lastValue'
            $entitiesData = $adapter->fetchEntities($entityName, $lastValue, 100);

            //Analyze the collected
            $this->processEntityData($state, $entitiesData);

            //Persist the updated state for this entity type
            $this->updateState($state);
        }
    }

    public function getServiceAdapter($entityName)
    {
        if (array_key_exists($entityName, $this->serviceAdaptersByEntityName)) {

            return $this->serviceAdaptersByEntityName[$entityName];

        } else {
            throw new \RuntimeException('No service adapter available for entity ' . $entityName);
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

    public function findLocalEntity($entityName, $remoteId)
    {
        //If ID mapping is active we first test if the id exists in the local id <> remote id mapping
        if ($this->config['use_id_mapping']) {

            $localEntityId = $this->gateway->findLocalId($entityName, $remoteId);

            return $localEntityId;  //Todo retrieve real entity but we don't need this yet
        }
    }

    public function updateState(SyncState $state)
    {
        $this->gateway->updateState($state);
    }

    protected function processEntityData(SyncState $state, array $entitiesData)
    {
        if (count($entitiesData) == 0) return;

        $allEntitiesResolved = false;

        foreach ($entitiesData as $entityData) {

            $resolved = true;

            //If an entity requires dependencies, initiate dependency resolving
            // Depending on the configuration it will be resolved inmediately or delayed
            if ( $unresolvedDependencies = $entityData->getUnresolvedDependencies() ) {
               $resolved = $this->processEntityDataDependencies($entityData, $unresolvedDependencies);
            }

            if ( true == $resolved) {
                //Transform the entity data into a real entity
                $localEntity = $this->transformEntityData($entityData);

            } else {
                $allEntitiesResolved = false;
            }
        }

        //Get the last entity
        $lastEntityData = end($entitiesData);

        $state->setLastValue($lastEntityData->getEntityId());  //TODO: make more generic
    }

    protected function maintainIdMapping($localEntity, EntityData $remoteEntityData)
    {
        $this->gateway->updateIdMapping($remoteEntityData->getEntityName(), $localEntity->getId(), $remoteEntityData->getEntityId());
    }

    protected function processEntityDataDependencies($entityData, $unresolvedDependencies)
    {
        foreach ($unresolvedDependencies as $entityName => $dependencyData)
        {
            //Get the remote identifier for this dependency (eg. for an order the dependency
            //'customer' would could have remote id 1239
            $remoteId = (string)$dependencyData['data'];

            //Check if we do not already have a local copy of the dependent entity
            $localEntity = $this->findLocalEntity($entityName, $remoteId);

            //So we don't have it yet, let us request the dependency
            if (null == $localEntity) {

                //Do we request the dependency to be discovered now or later (add it to a queue)?
                if (false ==  $this->config['delay_dependency_processing']) {

                    $entity = $this->resolveRemoteEntity($entityName, $remoteId);

                    if (null != $entity) {
                        $entityData->setDependencyReference($entityName, $entity);
                    }

                } else {
                   // Register the request to the entity queue  with the remote id and referencing entity
                    $this->queues[$entityName] = array($remoteId, $entityData);
                }
            }
        }

    }

    protected function resolveRemoteEntity($entityName, $remoteId)
    {
        $serviceAdapter = $this->getServiceAdapter($entityName);

        //Get the entity data from the remote system in it's raw form (eg. xml data)
        $entityData = $serviceAdapter->fetchEntity($entityName, $remoteId);

        //Apply transformation and retrieve the local entity
        return  $this->transformEntityData($entityData, $serviceAdapter);
    }

    protected function transformEntityData(EntityData $entityData, $serviceAdapter = null) {

        if (null == $serviceAdapter) {
            $serviceAdapter = $this->getServiceAdapter($entityData->getEntityName());
        }

        //Use the service adapter to transform the entity data
        $localEntity = $serviceAdapter->transformEntityData($entityData);

        // If the manager is responsible for tracking references between local and remote entity ids
        // then maintain the id mapping relationship
        if ($this->config['use_id_mapping']) {
            $this->maintainIdMapping($localEntity, $entityData);
        }
    }
}