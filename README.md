Vespolina Syncer Library
======================

[![Build Status](https://secure.travis-ci.org/vespolina/syncer.png?branch=master)](http://travis-ci.org/vespolina/syncer)

This library is part of the [Vespolina Ecommerce Framework](http://vespolina.org/)
and licensed under the [MIT License](LICENSE).

## Description

This library handles synchronisation of entities (eg. products, orders, invoices, content) from a remote service into a local application.
It furthers allows dependent entities to be synchronized as well.   For instance in order to retrieve an invoice you would also need remote customer information and referenced products.

Partial retrieved entities and dependent entities can be persisted to a gateway allowing the process to be halted at any time and picked up later.

## Requirements

None ;-)

## Documentation

Example usage

```php
// Create a new manager and persist data in memory
$syncManager = new SyncManager(new SyncMemoryGateway(), new EventDispatcher(), $this->logger);

// Instantiate your own service adapter, for example for the ZOHO api
$zohoServiceAdapter = new ZohoServiceAdapter($this->config, $this->logger);,

// Register the service adapter.  The service adapter will indicate it supports the 'invoice' entity
$syncManager->addServiceAdapter($zohoServiceAdapter);

// Register a local object manager to retrieve local customer instances from the database
$syncManager->addLocalEntityRetriever('customer', $customerManager, 'findById');

// Start synchronisation for entity name 'invoice'
$syncManager->execute(array('invoice'));
```

The service adapter needs to implement abstract methods *fetchEntities* , *fetchEntity* and *transformEntityData*.

*fetchEntities* downloads the entities from a remote service and creates for each remote entity an EntityData instance.
This instance contains the name of the entity (eg. 'invoice'), the remote identification (eg. zoho id '234324') and raw entity information (eg. xml or json data).

When dependencies are detected by the sync manager it will first check if the dependency already exists in the local application.
If this isn't the case the configured service adapter for the dependent remote entity will be used to retrieve and create the entity local.
For instance the zoho invoice entity requires the zoho customer entity as well.
Therefore the remote customer information needs to be retrieved first and a local customer instance needs to be created and persisted.  Only then the invoice can be created.

The system can directly resolve dependencies when detected or first store the partial data of the main entity (eg. 'invoice').

Resolve dependencies inmediately:

1. For each remote invoice
2. Register partial invoice data in EntityData,
3. Resolve dependent remote entity 'customer' 12313
3. Resolve dependent remote entity 'product' 222 and 'product' 333
4. When local 'customer' and 'product entities have been created, use that to create the invoice entity
5. End for each remote invoice

Delay resolving dependencies:

1. For each remote invoice
2. Register partial invoice data in EntityData,
3. End for each remote invoice
4. For each unresolved dependency
5. Resolve dependency
6. End for each unresolved dependency
7. Retrieve partial entity date invoices
8. transform into a real invoice
9. When local 'customer' and 'product entities have been created, use that to create the invoice entity

Having all dependencies resolved the requested entity (eg 'invoice') is created using the *transformEntityData* method of the service adapter.

You can also provide configuration options to the manager to define your synchronisation:

```php
$yamlParser = new Parser();
$config = $yamlParser->parse(file_get_contents(__DIR__ . '/config.yml'));
$this->manager = new SyncManager($gateway, $dispatcher, $logger, $config['syncer']);
```

And your sync configuration file could look like:

```yml
syncer:
    direction: download
    default_direction: download
    use_id_mapping: true
    default_remote: demo_system_1
    delay_dependency_processing: false
    entities:
        customer:
            strategy:  changed_at
        product:
            strategy: incremental_id
        invoice:
            strategy: incremental_id
            dependencies:
                - customer
                - product
    remotes:
        demo_system_1:
            adapter: Vespolina\Sync\Adapter\RemoteAdapter
```

For the install guide and reference, see:

* [Syncer documentation](http://docs.vespolina.org/components/syncer.html)

## Contributing

Pull requests are welcome. Please see our
[CONTRIBUTING](http://vespolina.org/contributing/guide)
guide.

Unit and/or functional tests exist for this library. See the
[Testing documentation]((http://vespolina.org/contributing/testing)
for a guide to running the tests.

Thanks to
[everyone who has contributed](https://github.com/vespolina/syncer/contributors) already.
