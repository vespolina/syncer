<?php

/**
 * (c) 2011 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\Tests\Sync\Functional;

use Vespolina\Sync\ServiceAdapter\AbstractServiceAdapter;
use Vespolina\Sync\Entity\EntityData;
use Vespolina\Tests\Sync\Functional\Entity\LocalProduct;
use Vespolina\Tests\Sync\Functional\Entity\LocalProductCategory;
use Vespolina\Tests\Sync\Functional\Entity\RemoteProduct;
use Vespolina\Tests\Sync\Functional\Entity\RemoteProductCategory;

/**
 * A simple remote service adapter faking the retrieval of external entities
 * , eg. in the real world it might be an adaptor to web services
 * such as Zoho, Magento Go, ...
 *
 * This dummy test provider supports the synchronization of remote products
 * and associated product category
 */
class SimpleRemoteServiceAdapter extends AbstractServiceAdapter
{
    protected $remoteProducts;
    protected $remoteCategories;
    protected $size;
    protected $lastValue;

    public function addProduct($remoteProduct)
    {
        if (null == $this->remoteProducts) {
            $this->remoteProducts = array();
        }
        $this->remoteProducts[$remoteProduct->id] = $remoteProduct;
    }

    public function addProductCategory($remoteProductCategory)
    {
        if (null == $this->remoteCategories) {
            $this->remoteCategories = array();
        }
        $this->remoteCategories[$remoteProductCategory->name] = $remoteProductCategory;
    }

    public function setupFakeData()
    {
        for ($i = 1; $i <= 20;$i++) {
            // Create a remote product category
            $cat = new RemoteProductCategory();
            $cat->name = 'cat' . $i;
            $this->addProductCategory($cat);

            // The primary object we will be syncing
            $remoteProduct = new RemoteProduct();
            $remoteProduct->id = $i;

            // Setup a depending object requiring individual  syncing
            $remoteProduct->category = $cat;

            $this->addProduct($remoteProduct);
        }
    }

    public function fetchEntity($entityName, $remoteId)
    {
        switch ($entityName) {
            case 'product':
                if (array_key_exists($remoteId, $this->remoteProducts)) {
                    return new EntityData($entityName, $remoteId, '<xml>...blablabla...</xml>');
                }
                break;
            case 'category':
                if (array_key_exists($remoteId, $this->remoteCategories)) {
                    return new EntityData($entityName, $remoteId, '<xml>...blablabla...</xml>');
                }
                break;
        }
    }

    public function fetchEntities($entityName, $lastValue, $size)
    {
        $out = array();

        switch ($entityName) {
            case 'product':
                // Simple naive implementation comparing the entity id
                foreach ($this->remoteProducts as $remoteProduct) {

                    if (null != $size && count($out) == $size) return $out;

                    if ($remoteProduct->id > $lastValue || null == $lastValue) {
                        $ed = new EntityData($entityName, $remoteProduct->id);

                        // Indicate to the sync manager that we need the category dependency
                        $ed->addDependency('category', 'cat' . $remoteProduct->id);
                        $out[] = $ed;
                    }
                }
                break;

            case 'category':
                // Even more naive
                foreach ($this->remoteCategories as $remoteCat) {

                    if (null != $size && count($out) == $size) return $out;

                    $out[] = new EntityData($entityName, $remoteCat->name);
                }
                break;
        }

        return $out;
    }

    public function transformEntityData(EntityData $entityData)
    {
        switch ($entityData->getEntityName()) {
            case 'product':
                $product = new LocalProduct();
                $product->id = 'local' . $entityData->getEntityId();   //In reality the local persistence gateway would generate local id

                $product->category = $entityData->getDependencyReference('category');

                return $product;

            case 'category':
                $cat = new LocalProductCategory();
                $cat->name = $entityData->getEntityId();   //In reality the local persistence gateway would generate local id

                return $cat;
        }
    }
}
