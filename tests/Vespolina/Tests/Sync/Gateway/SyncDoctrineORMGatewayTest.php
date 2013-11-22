<?php

/**
 * (c) 2011 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\Tests\Sync\Gateway;

use Doctrine\Common\Persistence\Mapping\Driver\SymfonyFileLocator;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use Vespolina\Sync\Gateway\SyncDoctrineORMGateway;

class SyncDoctrineORMGatewayTest extends SyncGatewayTestCommon
{
    public function setUp()
    {
        $dbParams = array(
            'driver'   => 'pdo_sqlite',
            'user'     => 'root',
            'password' => '',
            'path'     => __DIR__.'/fixture.db',
        );

        $locatorXml = new SymfonyFileLocator(
            array(
                __DIR__ . '/../../../../../lib/Vespolina/Sync/Mapping' => 'Vespolina\\Sync\\Entity',
            ),
            '.orm.xml'
        );

        $config = Setup::createConfiguration();
        $config->setMetadataDriverImpl(new XmlDriver($locatorXml));
        $em = EntityManager::create($dbParams, $config);
        $this->generateSchema($em);
        $this->gateway = new SyncDoctrineORMGateway($em, 'Vespolina\Entity\Action\Action');

        parent::setUp();
    }

    protected function generateSchema($em)
    {
        // Get the metadata from the application to create the schema.
        $metadatas = $this->getMetadatas($em);

        if (!empty($metadatas)) {
            // Create SchemaTool
            $tool = new SchemaTool($em);
            $tool->createSchema($metadatas);
        } else {
            throw \Exception('No Metadata Classes to process.');
        }
    }

    protected function getMetadatas($em)
    {
        return $em->getMetadataFactory()->getAllMetadata();
    }
}
