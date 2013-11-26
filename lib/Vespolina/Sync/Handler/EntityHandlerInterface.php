<?php

namespace Vespolina\Sync\Handler;

/**
 * Interface to determine and handle dependencies for a given entity type
 *
 * @author Daniel Kucharski <daniel@vespolina.org>
 */
interface EntityHandlerInterface
{
    /**
     * Return true if the entity type has any dependency
     *
     * @param $entity
     * @return Boolean
     */
    public function hasDependencies($entity);

    /**
     * Emit the request to resolve dependencies
     *
     * @param $entity
     * @return mixed List of dependencies to be resolved
     */
    public function emit($entity);

    /**
     * Merge resolved dependencies for the entity
     *
     * @param  array $resolvedDependencies
     * @return mixed
     */
    public function merge(array $resolvedDependencies);
}
