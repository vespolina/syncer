<?php

/**
 * (c) 2011 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\Sync\Gateway;

use Doctrine\ODM\MongoDB\DocumentManager;
use Vespolina\Sync\Entity\EntityData;
use Vespolina\Sync\Entity\IdMap;
use Vespolina\Sync\Entity\SyncStateInterface;

class SyncDoctrineMongoDBGateway implements SyncGatewayInterface
{
    protected $entityDataClass;
    protected $stateClass;
    protected $dm;
    protected $idMapClass;

    /**
     * @param DocumentManager $dm
     * @param string $entityDataClass
     * @param string $stateClass
     * @param string $idMapClass
     */
    public function __construct(
        DocumentManager $dm,
        $entityDataClass = 'Vespolina\Sync\Entity\EntityData',
        $stateClass = 'Vespolina\Sync\Entity\SyncState',
        $idMapClass = 'Vespolina\Sync\Entity\IdMap'
    )
    {
        $this->dm = $dm;
        $this->entityDataClass = $entityDataClass;
        $this->stateClass = $stateClass;
        $this->idMapClass = $idMapClass;
    }

    public function findLocalId($entityName, $remoteId)
    {
        $idMap = $this->dm->createQueryBuilder($this->idMapClass)
            ->field('entityName')->equals($entityName)
            ->field('remoteId')->equals($remoteId)
            ->getQuery()
            ->getSingleResult();

        if (null != $idMap) {
            return $idMap->getLocalId();
        }

        return null;
    }

    public function findStateByEntityName($entityName)
    {
        return $this->dm->createQueryBuilder($this->stateClass)
            ->field('entityName')->equals($entityName)
            ->getQuery()
            ->getSingleResult();
    }

    public function updateIdMapping($entityName, $localId, $remoteId)
    {
        $this->dm->persist(new IdMap($entityName, $localId, $remoteId, 'service'));
        $this->dm->flush();
    }

    public function updateState(SyncStateInterface $state)
    {
        $this->dm->persist($state);
        $this->dm->flush();
    }

    public function updateEntityData(EntityData $entityData)
    {
        $this->dm->persist($entityData);
        $this->dm->flush();
    }
}