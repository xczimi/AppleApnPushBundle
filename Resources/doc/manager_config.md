qApn Push Manager Configuration
==============================

Each manager can configure parameters:

* payload_factory (Payload factory options)
* connection (Connection options)
* sandbox (Enable sandbox mode)
* certificate (Path to certificate file)
* passphrase (Passphrase for you certificate file)
* logger

#### payload_factory

You can set JSON_UNESCAPED_UNICODE for json_encode payload data

Example:

```yml
payload_factory:
    json_unescaped_unicode: true
```

**Attention**
> This option can configure only PHP >= 5.4

#### connection

You can set read time selected response form apple servers.
[stream_select](http://php.net/manual/en/function.stream-select.php)

Example:

```yml
connection:
    read_time: [ 1, 0 ]
```

First parameter: **integer** seconds

Second parameter: **integer** miliseconds

#### logger

You can configure logger system for manager

Example create monolog handler:

```yml
monolog:
    handlers:
        # Your handler name
        handler_name:
            type: stream
            path: "%kernel.logs_dir%/%kernel.environment%/apn_push.log"
            level: info
            channels:
                type: inclusive # Set inclusive for disable write another service to this handler
```

And add handler to logger configuration:

```yml
logger:
    handlers: [ handler_name ]
    name: apn_push_notification # Optional parameter
```

For more information see `config:dump-reference`

```bash
$ php app/console config:dump-reference AppleApnPushBundle
```

The following documents are available:

- [Usage many managers](many_managers.md)
- [Default parameters](default_parameters.md)