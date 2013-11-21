<?php

namespace Vespolina\Sync\ServiceAdapter;

/**
 * Interface to determine and handle dependencies for a given entity type
 *
 * @author Daniel Kucharski <daniel@vespolina.org>
 */
abstract class AbstractServiceAdapter implements ServiceAdapterInterface
{
    protected $config;
    protected $logger;
    protected $supportedEntities;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $supportedEntities, array $config = array(), $logger = null)
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->supportedEntities = $supportedEntities;
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedEntities()
    {
        return $this->supportedEntities;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsEntity($entityName)
    {
        return in_array($this->supportedEntities, $entityName);
    }
}