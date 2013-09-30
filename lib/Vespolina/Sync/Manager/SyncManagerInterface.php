<?php

/**
 * (c) 2011 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\Sync\Manager;

use Vespolina\Sync\Entity\SyncStateInterface;

/**
 * An interface to manage the synchronization state
 *
 * @author Daniel Kucharski <daniel-xerias.be>
 */
interface SyncManagerInterface
{

    /**
     * Retrieve the synchronisation state for the entity
     *
     * @param $entityName
     * @return SyncStateInterface
     */
    function getState($entityName);
}
