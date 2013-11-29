<?php

/**
 * (c) 2011 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\Sync\Gateway;

use Doctrine\ORM\EntityManager;
use Vespolina\Sync\Entity\EntityData;
use Vespolina\Sync\Entity\IdMap;
use Vespolina\Sync\Entity\SyncStateInterface;

/**
 * Doctrine ORM implementation of the Gateway for synchronization
 *
 * @author Luis Cordova <cordoval@gmail.com>
 */
class SyncDoctrineORMGateway implements SyncGatewayInterface
{
    protected $entityDataClass;
    protected $stateClass;
    protected $em;
    protected $idMapClass;

    /**
     * @param EntityManager $em
     * @param string        $entityDataClass
     * @param string        $stateClass
     * @param string        $idMapClass
     */
    public function __construct(
        EntityManager $em,
        $entityDataClass = 'Vespolina\Sync\Entity\EntityData',
        $stateClass = 'Vespolina\Sync\Entity\SyncState',
        $idMapClass = 'Vespolina\Sync\Entity\IdMap'
    )
    {
        $this->em = $em;
        $this->entityDataClass = $entityDataClass;
        $this->stateClass = $stateClass;
        $this->idMapClass = $idMapClass;
    }

    /**
     * {@inheritdoc}
     */
    public function findLocalId($entityName, $remoteId)
    {
        $qb = $this->em->createQueryBuilder();
        $idMap = $qb
                ->select('i')
                ->from($this->idMapClass, 'i')
                ->where($qb->expr()->andX(
                    $qb->expr()->eq('i.entityName', ':entityName'),
                    $qb->expr()->eq('i.remoteId', ':remoteId')
                )
            )
            ->setParameters(array(
                    'entityName' => $entityName,
                    'remoteId' => $remoteId,
                )
            )
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (null != $idMap) {
            return $idMap->getLocalId();
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function findStateByEntityName($entityName)
    {
        $qb = $this->em->createQueryBuilder();

        return $qb
            ->select('s')
            ->from($this->stateClass, 's')
            ->where($qb->expr()->eq('s.entityName', ':entityName'))
            ->setParameters(array('entityName' => $entityName))
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function updateIdMapping($entityName, $localId, $remoteId)
    {
        $repo = $this->em->getRepository($this->idMapClass);
        $idMap = $repo->findOneBy(array(
                'entityName' => $entityName,
                'localId' => $localId,
                'remoteId' => $remoteId,
            )
        );
        if (null == $idMap) {
            $idMap = new IdMap($entityName, $localId, $remoteId, 'service');
        }

        $this->em->persist($idMap);
        $this->em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function updateEntityData(EntityData $entityData)
    {
        $this->em->persist($entityData);
        $this->em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function updateState(SyncStateInterface $state)
    {
        $this->em->persist($state);
        $this->em->flush();
    }
}
