Vespolina Syncer Library
======================

[![Build Status](https://secure.travis-ci.org/vespolina/syncer.png?branch=master)](http://travis-ci.org/vespolina/syncer)

This library is part of the [Vespolina Ecommerce Framework](http://vespolina.org/)
and licensed under the [MIT License](LICENSE).

## Description

This library handles sychronisation of entities (eg. products, orders, invoices, content) from a remote service into a local application.
It furthers allows dependent to be synchronized as well.   For instance in order to retrieve an invoice entity you would also need the customer and referenced products.

Partial retrieved entities and dependent entities can be persisted to a gateway allowing the process to be halt at any time and picked up later.



## Requirements

None ;-)

## Documentation

Example usage

```
// Create a new manager and manage persistency in memory
$syncManager = new SyncManager(new SyncMemoryGateway(), new EventDispatcher(), $this->logger );

// Instantiate your own service adapter, for example to the ZOHO api
$zohoServiceAdapter = new ZohoServiceAdapter($this->config, $this->logger);,

//Register the service adapter.  The service adapter will indicate it supports the 'invoice' entity
$this->syncManager->addServiceAdapter($zohoInvoiceServiceAdapter);

//Start synchronisation for entity name 'invoice'
$this->syncManager->execute(array('invoice'));
```

The service adapter needs to implement abstract methods fetchEntities, fetchEntity and transformEntityData.

fetchEntities downloads the entities from a remote service and creates for each remote entity an EntityData instance.
This instance contains the name of the entity (eg. 'invoice'), the remote identification (eg. zoho id '234324') and raw entity information (eg. xml or json data).

The service adapter can then register additional dependencies to be resolved.  For instance the zoho invoice entity requires the zoho customer entity.

When dependencies are detected by the sync manager it will first check if the dependency already exists in the local application.
If this isn't the case the configured service adapter for the dependent remote entity will be used to retrieve and create the entity local.

The system can can directly resolve dependencies when detected or first store the partial data of the main entity (eg. 'invoice').

Resolve dependencies inmediately:

1) For each remote invoice
2) Register partial invoice data in EntityData,
3) Resolve dependent remote entity 'customer' 12313
3) Resolve dependent remote entity 'product' 222 qnd 'product' 333
4) When local 'customer' and 'product entities have been created, use that to create the invoice entity
5) End for each remote invoice

Delay resolving dependencies:

1) For each remote invoice
2) Register partial invoice data in EntityData,
3) End for each remote invoice
4) For each unresolved dependency
5) Resolve dependency
6) End for each unresolved dependency
7) Retrieve partial entity date invoices
8) transform into a real invoice
9) When local 'customer' and 'product entities have been created, use that to create the invoice entity

Having all dependencies resolved the requested entity (eg 'invoice') is created using the transformEntityData method of the service adapter.






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