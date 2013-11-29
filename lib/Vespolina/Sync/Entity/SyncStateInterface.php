<?php

/**
 * (c) 2011 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\Sync\Entity;

/**
 * An interface to manage the synchronization state per entity collection
 *
 * @author Daniel Kucharski <daniel@vespolina.org>
 */
interface SyncStateInterface
{
    /**
     * @return string
     */
    public function getEntityName();

    /**
     * @param string $lastValue
     */
    public function setLastValue($lastValue);
}
