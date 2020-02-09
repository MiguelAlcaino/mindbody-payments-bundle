Installation
========

Configure
=======
Add routes
-------- 
Create a new file `mindbody_payments.yaml` under `config/routes/`. The file should look like this

```
mindbody_routes:
    resource: "@MiguelAlcainoMindbodyPaymentsBundle/Resources/routes/mindbody_payments.yaml"
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
    payment_response_route: 'your_successful_payment_route_name'
    siteName:'Your site name' # This will be used in templates
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

Add global variables to Twig in `config/packages/twig.yaml`
----------------
```
imports:
    - { resource: '@MiguelAlcainoMindbodyPaymentsBundle/Resources/config/twig.yaml' }
```

Configure requested services in config/services.yaml
-----------
 - Add an alias to the class `MiguelAlcaino\PaymentGateway\Interfaces\PaymentGatewayRouterInterface`.
  The intention of this service is to provide route names used in the MindbodyBundle but coming from the 
  paymentGateway. The payment gateway bundle implemented in the project should provide a service that cover 
  this, but in case it does not provide it you have to crate a service 
  that implements `MiguelAlcaino\PaymentGateway\Interfaces\PaymentGatewayRouterInterface`
 
 This is an example code:
 ```
 services:
     MiguelAlcaino\PaymentGateway\Interfaces\PaymentGatewayRouterInterface: '@miguelalcaino.migs.payment_service'
 ```

Prices
------
By default, when prices are being displayed in twig they will be using the `price_format` filter which by default has 2 decimal separators, 
'.' as decimal point and '.' as thousand separator. If you want to change these values, add the following parameters to your `config/services.yaml`:
 - price.default_decimals (int)
 - price.default_decimal_point (string)
 - price.default_thousand_separator (string)

Configure Symfony Security:
======================

This adds the security to your MinbdodyPaymentsBundle's Admin panel. The login and logout urls should be `/adm/login` 
and `/adm/logout`. They start with `adm` so they don't crash with the security firewall's rule `/admin`.

Modify config/security.yaml
-------
Your `config/security.yaml` should look like this:
```
security:
    # https://symfony.com/doc/current/security.html#where-do-users-come-from-user-providers
    encoders:
        MiguelAlcaino\MindbodyPaymentsBundle\Entity\User:
            algorithm: argon2i
    providers:
        # used to reload user from session & other features (e.g. switch_user)
        admin_provider:
            entity:
                class: MiguelAlcaino\MindbodyPaymentsBundle\Entity\User
                property: email
        mindbody_user_provider:
            entity:
                class: MiguelAlcaino\MindbodyPaymentsBundle\Entity\Customer
                property: email
    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false
        main_admin:
            provider: admin_provider
            pattern: ^/(admin|adm)
            anonymous: true
            guard:
                authenticators:
                    - MiguelAlcaino\MindbodyPaymentsBundle\Security\LoginFormAuthenticator
            logout:
                path: admin_logout
                target:  admin_login

        mindbody_purchase_area:
            provider: mindbody_user_provider
            anonymous: true
            guard:
                authenticators:
                    - MiguelAlcaino\MindbodyPaymentsBundle\Security\MindbodyPurchaseAreaAuthenticator
            logout:
                path: mindbody_logout
                target: http://www.ogb.cl

            # activate different ways to authenticate

            # http_basic: true
            # https://symfony.com/doc/current/security.html#a-configuring-how-your-users-will-authenticate

            # form_login: true
            # https://symfony.com/doc/current/security/form_login_setup.html

    role_hierarchy:
        ROLE_SUPER_ADMIN: [ROLE_ADMIN]
    # Easy way to control access for large sections of your site
    # Note: Only the *first* access control that matches will be used
    access_control:
        - { path: ^/admin, roles: ROLE_ADMIN }
        # - { path: ^/profile, roles: ROLE_USER }

```

Roles explanation
-----------------
 - `ROLE_ADMIN`: It is used to access to all the routes that are prefixed by `/admin`.
 - `ROLE_SUPER_ADMIN`: It is used inside the admin panel to add or delete admin users.

**Remember**: These security and users has nothing to do with Mindbody's users, but with the users allowed to access to the admin panel.

Widget setup (the one that shows the system inside a popup)
================
Add to your `.env.local` 

```
URL_WHERE_WIDGET_IS_DISPLAYED=https://www.yoursite.com/schedule-page-with-wdget
```
- `URL_WHERE_WIDGET_IS_DISPLAYED`: This env variable is the url where the widget will be displayed.

Add in the page that will display the widget the javascript code seen here: `src/Resources/views/widget/testIndex.html.twig`.
This javascript code is supposed to work along the javascript code from mindbody. It hacks the `Book` buttons and makes them point
to the payment system inside a widget.


This javascript code accepts two query variables in the url:

 - `show_popup=1` will trigger the popup
 - `popup_u` is the paypment system's url base64 encoded
