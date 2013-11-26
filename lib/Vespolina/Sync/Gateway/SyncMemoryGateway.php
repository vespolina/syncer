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
 * A class to manage the synchronization state in memory
 *
 * @author Daniel Kucharski <danie@vespolina.org>
 */
class SyncMemoryGateway implements SyncGatewayInterface
{
    protected $data;
    protected $entityData;
    protected $idMapping;

    public function __construct()
    {
        $this->data = array();
        $this->entityData = array();
        $this->idMapping = array();
    }

    /**
     * {@inheritdoc}
     */
    public function findLocalId($entityName, $remoteId)
    {
        if (!array_key_exists($entityName, $this->idMapping)) {
            return null;
        }

        if (!array_key_exists($remoteId, $this->idMapping[$entityName])) {
            return null;
        }

        return $this->idMapping[$entityName][$remoteId];
    }

    /**
     * {@inheritdoc}
     */
    public function findStateByEntityName($entityName)
    {
        if (array_key_exists($entityName, $this->data)) {
            return $this->data[$entityName];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function updateIdMapping($entityName, $localId, $remoteId)
    {
        if (array_key_exists($entityName, $this->idMapping)) {
            $entityMapping = $this->idMapping[$entityName];
        } else {
            $entityMapping = array();
        }
        $entityMapping[$remoteId] = $localId;

        $this->idMapping[$entityName] = $entityMapping;
    }

    /**
     * {@inheritdoc}
     */
    public function updateEntityData(EntityData $entityData)
    {
        $this->entityData[$entityData->getKey()] = $entityData;
    }

    /**
     * {@inheritdoc}
     */
    public function updateState(SyncStateInterface $syncState)
    {
        $this->data[$syncState->getEntityName()] = $syncState;
    }
}
