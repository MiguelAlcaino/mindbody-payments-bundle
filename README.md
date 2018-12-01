Installation
========
``
Configure
=======
Add routes
-------- 
creating a new file `mindbody_payments.yaml` under `config/routes/`. The file should look like this

```
mindbody_payments:
    resource: '@MiguelAlcainoMindbodyPaymentsBundle/Controller/'
    type: annotation
```

Add config
-------
```
miguel_alcaino_mindbody_payments:
    handler:
        refund_handler: your.refund.handler.service.name
```
The refund handler class should implement `MiguelAlcaino\PaymentGateway\Interfaces\RefundHandlerInterface`

Add parameters
---------
```
parameters:
    enabled_payment_names: ['kushki']
```