Usage default parameters
========================

You can configure default parameters for all manager in you system.

Allowed default parameters:
* default_json_unescaped_unicode (Usage JSON_UNESCAPED_UNICODE in json_encode)
* default_read_time (Usage read time in [stream_select](http://php.net/manual/en/function.stream-select.php))
* default_sandbox_certificate_file
* default_certificate_file
* global_logger_handlers (This handlers sets to all managers)

Example:

```yml
apple_apn_push:
    default_json_unescaped_unicode: false
    default_read_time: [1, 0]
    default_sandbox_certificate_file: "/path/to/your/cert_sandbox.pem"
    default_certificate_file: "/path/to/your/certificate.pem"
    global_logger_handlers: [ apn_push_handler ]

    managers:
        default: ~

        sandbox:
            sandbox: true
            logger:
                name: sandbox
                handlers: [ apn_push_sandbox_handler ]
```
