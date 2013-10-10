<?php

/**
 * (c) 2011 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\Sync\Entity;

/**
 * An class to manage id mapping of an entity
 *
 * @author Daniel Kucharski <daniel@vespolina.org>
 */
class IdMap
{
    protected $id;
    protected $entityName;
    protected $localId;
    protected $remoteId;
    protected $remoteServiceName;

    public function __construct($entityName, $localId, $remoteId, $remoteServiceName = '')
    {
        $this->entityName = $entityName;
        $this->localId = $localId;
        $this->remoteId = $remoteId;
        $this->remoteServiceName = $remoteServiceName;
    }

    /**
     * @return mixed
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getLocalId()
    {
        return $this->localId;
    }

    /**
     * @return mixed
     */
    public function getRemoteId()
    {
        return $this->remoteId;
    }

    /**
     * @return string
     */
    public function getRemoteServiceName()
    {
        return $this->remoteServiceName;
    }


}
