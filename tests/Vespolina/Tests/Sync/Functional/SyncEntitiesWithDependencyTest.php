<?php

/**
 * (c) 2011 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\Tests\Sync\Functional;

class SyncEntitiesWithDependencyTest extends SyncBaseTestCase
{
    public function testSyncEntities()
    {
        $state = $this->manager->getState('product');
        $this->assertNull($state->getLastValue());

        // Perform synchronization
        $this->manager->execute(array('product'));

        // Verify that the log does not contain any issues
        $this->assertFalse($this->logHandler->hasErrorRecords(), 'Sync should not have any errors');
        // Test if all requested entities have been synced
        $state = $this->manager->getState('product');

        $this->assertEquals($state->getLastValue(), 20);
    }
}
