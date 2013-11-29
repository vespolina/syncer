<?php

/**
 * (c) 2011 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\Sync\Manager;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Vespolina\Sync\Entity\SyncState;
use Vespolina\Sync\Entity\SyncStateInterface;
use Vespolina\Sync\Gateway\SyncGatewayInterface;
use Vespolina\Sync\Handler\DefaultEntityHandler;
use Vespolina\Sync\ServiceAdapter\ServiceAdapterInterface;
use Vespolina\Sync\Entity\EntityData;

class SyncManager implements SyncManagerInterface
{
    protected $config;
    protected $dispatcher;
    protected $localEntityRetrievers;
    protected $handlers;
    protected $gateway;
    protected $serviceAdaptersByEntityName;
    protected $queues;

    public function __construct(SyncGatewayInterface $gateway, EventDispatcherInterface $dispatcher, LoggerInterface $logger, $config = array())
    {
        $this->dispatcher = $dispatcher;
        $this->localEntityRetrievers = array();
        $this->handlers = array();
        $this->logger = $logger;
        $this->gateway = $gateway;
        $this->serviceAdaptersByEntityName = array();
        $this->queues = array();

        $defaultConfig = array(
            'delay_dependency_processing' => false,
            'use_id_mapping'              => true,
        );
        $this->config = array_merge($config, $defaultConfig);

        // Setup the default entity handler
        $this->handlers['default'] = new DefaultEntityHandler();
    }

    /**
     * {@inheritdoc}
     */
    public function addLocalEntityRetriever($entityName, $retriever, $method = 'findId')
    {
        $this->localEntityRetrievers[$entityName] = array('retriever' => $retriever, 'method' => $method);
    }

    /**
     * {@inheritdoc}
     */
    public function addServiceAdapter(ServiceAdapterInterface $adapter)
    {
        // Store service adapters per entity name they support
        foreach ($adapter->getSupportedEntities() as $entityName) {
            $this->serviceAdaptersByEntityName[$entityName] = $adapter;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function execute(array $entityNames = array(), $size = 0)
    {
        foreach ($entityNames as $entityName) {
            // Prepare synchronization, retrieve the last key value we retrieved
            $state = $this->getState($entityName);
            $lastValue = $state->getLastValue();

            // Get the service adapter for this entity
            $adapter = $this->getServiceAdapter($entityName);

            if (null == $lastValue) {
                $message = 'Fetching ' . $entityName . ' initialized for the first time';
            } else {
                $message = 'Fetching ' . $entityName . ' starting at "' . $lastValue . '"';
            }

            $this->logger->info($message);

            // Fetch raw entity data after 'lastValue'
            $entitiesData = $adapter->fetchEntities($entityName, $lastValue, $size);

            // Analyze the collected entities
            $this->processEntityDataCollection($state, $entitiesData);

            // Persist the updated state for this entity type
            $this->updateState($state);
        }
    }

    /**
     * @param $entityName
     * @return \Vespolina\Sync\ServiceAdapter\ServiceAdapterInterface
     * @throws \RuntimeException
     */
    public function getServiceAdapter($entityName)
    {
        if (array_key_exists($entityName, $this->serviceAdaptersByEntityName)) {
            return $this->serviceAdaptersByEntityName[$entityName];
        }

        throw new \RuntimeException('No service adapter available for entity ' . $entityName);
    }

    /**
     * {@inheritdoc}
     */
    public function getState($entityName)
    {
        $state = $this->gateway->findStateByEntityName($entityName);

        if (null == $state) {
            $state = new SyncState($entityName);
        }

        return $state;
    }

    /**
     * {@inheritdoc}
     */
    public function findLocalEntity($entityName, $remoteId)
    {
        // If id mapping is active we first test if the id exists in the local id <> remote id mapping
        if ($this->config['use_id_mapping']) {

            $localEntityId = $this->gateway->findLocalId($entityName, $remoteId);

            if (null != $localEntityId) {
                return $this->retrieveLocalEntity($entityName, $localEntityId);
            }

            return $localEntityId;  // Todo retrieve real entity but we don't need this yet
        }

        return null;
    }

    protected function updateState(SyncStateInterface $state)
    {
        $this->gateway->updateState($state);
    }

    /**
     * Process the resulting entity data collection retrieved by a service adapter.
     * Test if any dependencies do exist and retrieve those dependencies if they haven't
     * been yet retrieved
     *
     * @param SyncStateInterface                  $state
     * @param \Vespolina\Sync\Entity\EntityData[] $entitiesData
     */
    protected function processEntityDataCollection(SyncStateInterface $state, array $entitiesData)
    {
        if (count($entitiesData) == 0) {
            return;
        }

        foreach ($entitiesData as $entityData) {
            $resolved = true;

            // If an entity requires dependencies, initiate dependency resolving
            // Depending on the configuration it will be resolved immediately or delayed
            if ($unresolvedDependencies = $entityData->getUnresolvedDependencies()) {
               $resolved = $this->processEntityDataDependencies($entityData, $unresolvedDependencies);
            }

            if (true == $resolved) {
                // Transform the entity data into a real entity
                $localEntity = $this->transformEntityData($entityData);
            } else {
                // Add to queue
                $this->gateway->updateEntityData($entityData);
            }
        }

        // Get the last entity
        $lastEntityData = end($entitiesData);

        $state->setLastValue($lastEntityData->getEntityId());  // TODO: use config
    }

    /**
     * Maintain the id mapping (local vs remote id)
     *
     * @param string $localEntity
     * @param EntityData $remoteEntityData
     */
    protected function maintainIdMapping($localEntity, EntityData $remoteEntityData)
    {
        $this->gateway->updateIdMapping($remoteEntityData->getEntityName(), $localEntity->getId(), $remoteEntityData->getEntityId());
    }

    /**
     * Deal with entity data dependencies
     *
     * @param \Vespolina\Sync\Entity\EntityData $entityData
     * @param array $unresolvedDependencies
     * @return Boolean
     */
    protected function processEntityDataDependencies(EntityData $entityData, $unresolvedDependencies)
    {
        $resolved = true;

        foreach ($unresolvedDependencies as $entityName => $dependencyData) {
            // Get the remote identifier for this dependency (eg. for an order the dependency
            // 'customer' would could have remote id 1239
            $remoteId = (string) $dependencyData['data'];

            // Check if we do not already have a local copy of the dependent entity
            $localEntity = $this->findLocalEntity($entityName, $remoteId);

            // So we don't have it yet, let us request the dependency
            if (null == $localEntity) {

                // Do we request the dependency to be discovered now or later (add it to a queue)?
                if (false == $this->config['delay_dependency_processing']) {

                    $localEntity = $this->resolveRemoteEntity($entityName, $remoteId);

                    if (null == $localEntity) {
                        $resolved = false;
                    }
                } else {
                    // Register the request to the entity queue with the remote id and referencing entity
                    $this->queues[$entityName] = array($remoteId, $entityData);
                }
            }

            $entityData->setDependencyReference($entityName, $localEntity);
            $this->gateway->updateEntityData($entityData);
        }

        return $resolved;
    }

    /**
     * Resolve and transform into a new local entity for the given remote entity and id
     *
     * @param  string $entityName
     * @param  mixed $remoteId
     * @return object
     */
    protected function resolveRemoteEntity($entityName, $remoteId)
    {
        $serviceAdapter = $this->getServiceAdapter($entityName);

        // Get the entity data from the remote system in it's raw form (eg. xml data)
        $entityData = $serviceAdapter->fetchEntity($entityName, $remoteId);

        // Apply transformation and retrieve the local entity
        $localEntity = $this->transformEntityData($entityData, $serviceAdapter);

        if (null != $localEntity) {
            $this->logger->debug('Resolved dependency ' . $entityName . ' "' . $remoteId . '"');
        } else {
            $this->logger->error('Failed to resolve dependency ' . $entityName . ' "' . $remoteId . '"');
        }

        return $localEntity;
    }

    /**
     * Retrieve a local entity
     *
     * @param string $entityName
     * @param $localId
     * @throws \RuntimeException
     * @return object|null
     */
    protected function retrieveLocalEntity($entityName, $localId)
    {
        if (!array_key_exists($entityName, $this->localEntityRetrievers)) {
            throw new \RuntimeException('No entity retriever defined for "' . $entityName . '"');
        }

        $retriever = $this->localEntityRetrievers[$entityName]['retriever'];
        $method = $this->localEntityRetrievers[$entityName]['method'];

        return $retriever->{$method}($localId);
    }

    /**
     * Transform the entity data into a local entity
     *
     * Optionally provide the service adapter to do the job
     *
     * @param  EntityData              $entityData
     * @param  ServiceAdapterInterface $serviceAdapter
     * @return object
     */
    protected function transformEntityData(EntityData $entityData, ServiceAdapterInterface $serviceAdapter = null)
    {
        if (null == $serviceAdapter) {
            $serviceAdapter = $this->getServiceAdapter($entityData->getEntityName());
        }

        // Use the service adapter to transform the entity data
        $localEntity = $serviceAdapter->transformEntityData($entityData);

        // If the manager is responsible for tracking references between local and remote entity ids
        // then maintain the id mapping relationship
        if ($this->config['use_id_mapping']) {
            $this->maintainIdMapping($localEntity, $entityData);
        }

        return $localEntity;
    }
}
