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
     * Returns if the entity type has any dependency
     *
     * @param $entity
     * @return Boolean
     */
    function hasDependencies($entity);

    /**
     * Emit the request to resolve dependencies
     *
     * @param $entity
     * @return mixed List of dependencies to be resolved
     */
    function emit($entity);

    /**
     * Merge resolved dependencies for the entity
     *
     * @param array $resolvedDependencies
     * @return mixed
     */
    function merge(array $resolvedDependencies);
}