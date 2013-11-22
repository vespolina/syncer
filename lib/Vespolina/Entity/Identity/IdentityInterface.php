<?php

namespace Vespolina\Entity\Identity;

/**
 * @author Daniel Kucharski <daniel@vespolina.org>
 * @author Richard Shank <richard@vespolina.org>
 */
interface IdentityInterface
{
    public function getVendor();
    public function getUsername();
}
