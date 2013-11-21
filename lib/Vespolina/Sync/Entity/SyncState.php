<?php

/**
 * (c) 2011 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\Sync\Entity;

/**
 * A class to manage the synchronization state of a collection of entities of the same type
 *
 * @author Daniel Kucharski <daniel@vespolina.org>
 */
class SyncState implements SyncStateInterface
{
    protected $active;
    protected $entityName;
    protected $lastValue;

    public function __construct($entityName)
    {
        $this->entityName = $entityName;
    }

    /**
     * @param mixed $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return mixed
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param mixed $entityName
     */
    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * @param mixed $lastValue
     */
    public function setLastValue($lastValue)
    {
        $this->lastValue = $lastValue;
    }

    /**
     * @return mixed
     */
    public function getLastValue()
    {
        return $this->lastValue;
    }
}
