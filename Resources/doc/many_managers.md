Usage many managers
===================

You can configure many manager with this bundle.

All manager must be specified in parameter: `apple_apn_push.notification_managers`

Example usage:
```yml
apple_apn_push:
    default_notification_manager: default
    notification_managers:
        default:
            certificate: "/path/to/your/certificate.pem"

        second_manager:
            certificate: "/path/to/your/certificate2.pem"
            sandbox: true
```

Getting manager from service container:

```php
$notificationManagers = $container->get('apple.apn_push');
$managerName = 'second_manager';
$manager = $notificationManagers->getManager($managerName);
```