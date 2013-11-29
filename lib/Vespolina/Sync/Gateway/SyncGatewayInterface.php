<?php

/**
 * (c) 2011 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\Sync\Gateway;

use Vespolina\Sync\Entity\EntityData;
use Vespolina\Sync\Entity\SyncStateInterface;

/**
 * An interface to manage the synchronization state
 *
 * @author Daniel Kucharski <daniel@vespolina.org>
 */
interface SyncGatewayInterface
{
    /**
     * Find the local id for the remote id identified by remote id
     *
     * @param $entityName
     * @param $remoteId
     * @return integer|null
     */
    public function findLocalId($entityName, $remoteId);

    /**
     * Retrieve the current state for the specified entity name
     *
     * @param $entityName
     * @return \Vespolina\Sync\Entity\SyncStateInterface|null
     */
    public function findStateByEntityName($entityName);

    /**
     * Update the id mapping between the local entity id and the remote entity id
     *
     * @param $entityName
     * @param $localId
     * @param $remoteId
     */
    public function updateIdMapping($entityName, $localId, $remoteId);

    /**
     * Create or update an entity data instance
     *
     * @param EntityData $entityData
     */
    public function updateEntityData(EntityData $entityData);

    /**
     * Update the synchronisation state of an entity collection
     *
     * @param  SyncStateInterface $syncState
     */
    public function updateState(SyncStateInterface $syncState);
}
