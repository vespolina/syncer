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
 * An class to manage the synchronization state in memory
 *
 * @author Daniel Kucharski <danie@vespolina.org>
 */
class SyncMemoryGateway implements SyncGatewayInterface
{
    protected $data;
    protected $idMapping;

    public function __construct()
    {
        $this->data = array();
        $this->idMapping = array();
    }

    public function findLocalId($entityName, $remoteId)
    {
        if (!array_key_exists($entityName, $this->idMapping))
            return;

        if (!array_key_exists($remoteId, $this->idMapping[$entityName]))
            return;
        return $this->idMapping[$entityName][$remoteId];

    }
    public function findStateByEntityName($entityName)
    {
        if (array_key_exists($entityName, $this->data)) {

            return $this->data[$entityName];
        }
    }

    public function getIdMapping()
    {
        return $this->idMapping;
    }

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

    public function updateState(SyncStateInterface $syncState) {

        $this->data[$syncState->getEntityName()] = $syncState;
    }

}
