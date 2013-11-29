<?php

/**
 * (c) 2011 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\Sync\Manager;

use Vespolina\Sync\ServiceAdapter\ServiceAdapterInterface;

/**
 * An interface to manage the synchronization state
 *
 * @author Daniel Kucharski <daniel@vespolina.org>
 */
interface SyncManagerInterface
{
    /**
     * Register a local entity retriever
     *
     * @param string $entityName
     * @param object $retriever  Manager or gateway to retrieve the entity
     * @param string $method     The method which will be called to retrieve the entity when the id is passed
     */
    public function addLocalEntityRetriever($entityName, $retriever, $method = 'findId');

    /**
     * Register a service adapter
     *
     * @param ServiceAdapterInterface $serviceAdapter
     */
    public function addServiceAdapter(ServiceAdapterInterface $serviceAdapter);

    /**
     * Execute synchronization for the given list of entities
     *
     * @param array        $entityNames
     * @param integer|null $size
     */
    public function execute(array $entityNames = array(), $size = 0);

    /**
     * Retrieve the synchronisation state for the entity
     *
     * @param string $entityName
     * @return \Vespolina\Sync\Entity\SyncStateInterface
     */
    public function getState($entityName);

    /**
     * Retrieve local entity, localId or null if not found
     *
     * @param string $entityName
     * @param $remoteId
     * @return object|mixed|null
     */
    public function findLocalEntity($entityName, $remoteId);
}
