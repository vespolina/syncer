<?php

/**
 * (c) 2011 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\Sync\Entity;

/**
 * An class to manage the synchronization of a given entity
 *
 * @author Daniel Kucharski <daniel@vespolina.org>
 */
class EntityData
{
    protected $entityName;
    protected $entityId;
    protected $data;
    protected $dependencies;
    protected $id;

    public function __construct($entityName, $entityId, $data = null)
    {
        $this->dependencies = array();
        $this->data = $data;
        $this->entityName = $entityName;
        $this->entityId = $entityId;

    }

    public function addDependency($entityName, $data, $reference = null)
    {
        $this->dependencies[$entityName] = array('data' => $data, 'reference' => $reference);
    }

    public function hasDependencies()
    {
        return count($this->dependencies) > 0;
    }

    public function getUnresolvedDependencies()
    {
        $unresolvedDependencies = array();

        foreach ($this->dependencies as $entityName => $dependency) {
            if (null == $dependency['reference']) {
                $unresolvedDependencies[$entityName] = $dependency;
            }
        }

        return $unresolvedDependencies;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getDependencyReference($entityName)
    {
        return $this->dependencies[$entityName]['reference'];
    }

    public function getDependencies()
    {
        return $this->dependencies;
    }

    public function getEntityId()
    {
        return $this->entityId;
    }

    public function getEntityName()
    {
        return $this->entityName;
    }

    public function getKey()
    {
        return $this->entityName . '.' . $this->entityId;
    }

    public function setDependencyReference($entityName, $reference)
    {
        $this->dependencies[$entityName]['reference'] = $reference;
    }
}
