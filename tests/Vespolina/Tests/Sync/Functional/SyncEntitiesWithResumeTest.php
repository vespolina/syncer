<?php

/**
 * (c) 2011 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\Tests\Sync\Functional;

class SyncEntitiesWithResumeTest extends SyncBaseTestCase
{
    public function testSyncEntities()
    {
        // Initially nothing should have yet been synced
        $state = $this->manager->getState('product');

        // Get the last key value, should be initially null
        $this->assertNull($state->getLastValue());

        // SYNC 1 : get the first 2 products
        $this->manager->execute(array('product'), 2);

        $state = $this->manager->getState('product');
        $this->assertEquals(2, $state->getLastValue(), 'only 2 should have been synced');

        // SYNC 2 : get the next six products
        $this->manager->execute(array('product'), 6);
        $this->assertEquals($state->getLastValue(), 8, 'now 6 more, that is 8');

        // SYNC 3 : get the rest of the products
        $this->manager->execute(array('product'), 222222);
        $this->assertEquals($state->getLastValue(), 20, '20 total products synced');

        // Verify that the log does not contain any issues
        $this->assertFalse($this->logHandler->hasErrorRecords(), 'Sync logs should not have any errors');
    }
}
