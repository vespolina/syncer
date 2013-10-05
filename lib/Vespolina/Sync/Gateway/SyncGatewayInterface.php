<?php

/**
 * (c) 2011 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\Sync\Gateway;

use Vespolina\Sync\Entity\SyncStateInterface;

/**
 * An interface to manage the synchronization state
 *
 * @author Daniel Kucharski <daniel-xerias.be>
 */
interface SyncGatewayInterface
{
    /**
     * Retrieve the current state for the specified entity name
     *
     * @param $entityName
     * @return Vespolina\Sync\Entity\SyncState
     */
    function findStateByEntityName($entityName);

    /**
     * Update the id mapping between the local entity id and remote entity id
     *
     * @param $entityName
     * @param $localId
     * @param $remoteId
     */
    function updateIdMapping($entityName, $localId, $remoteId);

    /**
     * Update the synchronisation state of an entity collection
     *
     * @param SyncStateInterface $syncState
     * @return mixed
     */
    function updateState(SyncStateInterface $syncState);
}
