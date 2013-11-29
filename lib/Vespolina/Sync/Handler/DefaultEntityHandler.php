<?php

namespace Vespolina\Sync\Handler;

/**
 * Interface to determine and handle dependencies for a generic entity
 *
 * @author Daniel Kucharski <daniel@vespolina.org>
 */
class DefaultEntityHandler implements EntityHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function hasDependencies($entity)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function emit($entity)
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function merge(array $resolvedDependencies)
    {
        return array();
    }
}
