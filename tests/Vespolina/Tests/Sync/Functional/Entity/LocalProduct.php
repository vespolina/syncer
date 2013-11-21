<?php

/**
 * (c) 2011 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
 
namespace Vespolina\Tests\Sync\Functional\Entity;

class LocalProduct
{
    public $id;
    public $category;

    public function getId()
    {
        return $this->id;
    }
}