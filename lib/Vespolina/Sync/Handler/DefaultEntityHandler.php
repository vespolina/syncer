<?php

namespace Vespolina\Sync\Handler;

/**
 * Interface to determine and handle dependencies for a generic entity
 *
 * @author Daniel Kucharski <daniel@vespolina.org>
 */
class DefaultEntityHandler implements EntityHandlerInterface
{
    public function hasDependencies($entity)
    {
        return false;
    }

    public function emit($entity)
    {
    }

    public function merge(array $resolvedDependencies)
    {
    }
}
