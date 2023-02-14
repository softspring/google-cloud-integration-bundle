# Integration with Cloud Logging

There is not a shared integration yet in this bundle. 

So, we recommend to include this code (adapt it to your needs) in your project: 

```yaml
# config/packages/monolog.yaml
when@prod:
    services:
        Monolog\Handler\PsrHandler:
            class: Monolog\Handler\PsrHandler
            arguments: [ '@Google\Cloud\Logging\PsrLogger' ]

        Google\Cloud\Logging\PsrLogger:
            class: Google\Cloud\Logging\PsrLogger
            factory: [ 'Google\Cloud\Logging\LoggingClient', 'psrBatchLogger' ]
            arguments: [ 'app', [ ] ]

    monolog:
        handlers:
            main:
                type: fingers_crossed
                action_level: error
                handler: nested
                excluded_http_codes: [404, 405]
                buffer_size: 50 # How many messages should be saved? Prevent memory leaks
                channels: ["!deprecation"]
            nested:
                type: service
                level: debug
                id: Monolog\Handler\PsrHandler
            console:
                type: console
                process_psr_3_messages: false
                channels: ["!event", "!doctrine"]
```
