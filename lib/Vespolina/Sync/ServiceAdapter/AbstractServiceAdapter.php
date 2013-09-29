<?php

namespace Vespolina\Sync\ServiceAdapter;

use Vespolina\Sync\ServiceAdapter\ServiceAdapterInterface;

/**
 * Interface to determine and handle dependencies for a given entity type
 *
 * @author Daniel Kucharski <daniel@vespolina.org>
 */
abstract class AbstractServiceAdapter implements ServiceAdapterInterface
{
    protected $supportedEntities;

    public function __construct(array $supportedEntities = array())
    {
        $this->supportedEntities = $supportedEntities;
    }

    public function getSupportedEntities()
    {
        return $this->supportedEntities;
    }

    public function supportsEntity($entityName)
    {
        return in_array($this->supportedEntities, $entityName);
    }
}