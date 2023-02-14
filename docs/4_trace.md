# Google Trace

This integration also brings the feature to trace some Symfony and Doctrine events.

## Configure Google Trace

This integration is not enabled by default, and some libraries must be present:

First install *opencensus* extension for php, see [documentation](https://opencensus.io/api/php/) page.

If you are using *Google App Engine standard environment**, you only need to enable extension in your *php.ini* file:

```ini
# php.ini
extension=opencensus
```

Also you need to install *opencensus/opencensus-exporter-stackdriver* package:

```bash
$ composer require opencensus/opencensus-exporter-stackdriver
```

## Enable Google Trace integration

To enable it, you must set **GCLOUD_TRACE** environment variable to 1.

As well as error reporting, the integration needs to be loaded before Symfony initialization, so **it's not an option to set it in your *.env* file**.

You must configure it for your system:

```bash
$ export GCLOUD_TRACE=1
```

If you are using **Docker**:

```
# Dockerfile
ENV GCLOUD_TRACE 1
```

or **docker-compose**:

```yaml
# docker-compose.yaml
services:
    fpm:
        environment:
            GCLOUD_TRACE: 1
```

Also you must add it to your deployment configuration. For example, if you are using Google App Engine, you must add it
to your config file:

```yaml
# app.yaml
env_variables:
    GCLOUD_TRACE: 1
```
