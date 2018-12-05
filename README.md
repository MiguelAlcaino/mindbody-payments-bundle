Installation
========

Configure
=======
Add routes
-------- 
Create a new file `mindbody_payments.yaml` under `config/routes/`. The file should look like this

```
mindbody_payments:
    resource: '@MiguelAlcainoMindbodyPaymentsBundle/Controller/'
    type: annotation
```

Add config
-------
Create a new file `mindbody_payments.yaml` under `config/packages.yaml`. The file should look like this

```
miguel_alcaino_mindbody_payments:
    handler:
        refund_handler: your.refund.handler.service.name
```
The refund handler class should implement `MiguelAlcaino\PaymentGateway\Interfaces\RefundHandlerInterface`

Add parameters to your `config/services.yaml`
---------
```
parameters:
    enabled_payment_names: ['kushki']
    login_success_route: 'your_route_name_after_successful_login'
    login_template: 'default/login.html.twig'
    payment_response_route: 'your_successful_payment_route_name'
```

Add .env variables
----------

```
MINDBODY_SOURCE_NAME='xxxxxx'
MINDBODY_SOURCE_PASSWORD='xxxxxxxx'
MINDBODY_ADMIN_USER='xxxxxxxxxx'
MINDBODY_ADMIN_PASSWORD='xxxxxx'
MINDBODY_SITE_IDS=[-99]
HOST=your.site.com
MAIN_HOST=https://www.site.com
PAYMENT_SYSTEM_URL=https://your.site.com
BOOKING_PAGE='reserva-tu-clase'
```

Configure FosUserBundle:
======================

Add config
-------
Create a new file `fos_user.yaml` under `config/packages.yaml` and add this content:
```
fos_user:
    db_driver: orm # other valid values are 'mongodb' and 'couchdb'
    firewall_name: main
    user_class: MiguelAlcaino\MindbodyPaymentsBundle\Entity\User
    from_email:
        address: "%mailer_user%"
        sender_name: "%mailer_user%"
```

Edit the config/framework.yaml file
-----
```
framework:
    templating:
        engines: ['twig', 'php']
```

Add `mailer_user` parameter in `config/services.yaml`
--------
```
parameters:
    mailer_user: youemail@example.com
```

Add Routes
----------
Create a new file `fos_user.yaml` under `config/routes/`. The file should look like this
```
fos_user:
    resource: "@FOSUserBundle/Resources/config/routing/all.xml"
```
