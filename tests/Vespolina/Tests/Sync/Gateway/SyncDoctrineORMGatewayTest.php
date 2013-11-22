<?php

/**
 * (c) 2011 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\Tests\Sync\Gateway;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Persistence\Mapping\Driver\SymfonyFileLocator;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\ORM\Configuration;
use Vespolina\Sync\Gateway\SyncDoctrineORMGateway;

class SyncDoctrineORMGatewayTest extends SyncGatewayTestCommon
{
    public function setUp()
    {
        $config = new Configuration();
        $config->setProxyDir(sys_get_temp_dir());
        $config->setProxyNamespace('Proxies');

        $locatorXml = new SymfonyFileLocator(
            array(
                __DIR__ . '/../../../../../lib/Vespolina/Sync/Mapping' => 'Vespolina\\Sync\\Entity',
            ),
            '.orm.xml'
        );

        $xmlDriver = new XmlDriver($locatorXml);

        $config->setMetadataDriverImpl($xmlDriver);
        $config->setMetadataCacheImpl(new ArrayCache());
        $config->setAutoGenerateProxyClasses(true);
        $doctrineODM = EntityManager::create(null, $config);
        $this->gateway = new SyncDoctrineORMGateway($doctrineODM, 'Vespolina\Entity\Action\Action');

        parent::setUp();
    }
}
