Usage command line
==================

Your can send push with command line tools.

Commands:

* apple:apn-push:manager-list
* apple:apn-push:send

#### apple:apn-push:manager-list

View all apn push manager in your system

Example:

```bash
$ php app/console apple:apn-push:manager-list
```

#### apple:apn-push:send

Send message to device usage `device token`

Base example:

```bash
$ php app/console apple:apn-push:send DEVICE_TOKEN "Hello world =))"
```

You can send another options in aps data:

* sound
* badge

This options is optional.

Example:

```bash
$ php app/console apple:apn-push:send DEVICE_TOKEN "Hello world =))" --sound=file_name.acc --badge=5
```

And you can select push notification manager, if your system have managers more than one

Example:

```bash
$ php app/console apple:apn-push:send DEVICE_TOKEN "Hello world =))" --manager=MANAGER_NAME
```