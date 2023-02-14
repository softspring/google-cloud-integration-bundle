# Integration with Error reporting

By default, *google/cloud-error-reporting* package is installed but it's not enabled by default.

To enable it, you must set **GCLOUD_ERROR_REPORTING** environment variable to 1.

Error reporting is configured before Symfony initialization, so **it's not an option to set it in your *.env* file**.

You must configure it for your system:

```bash
$ export GCLOUD_ERROR_REPORTING=1
```

If you are using **Docker**:

```
# Dockerfile
ENV GCLOUD_ERROR_REPORTING 1
```

or **docker-compose**:

```yaml
# docker-compose.yaml
services:
    fpm:
        environment:
            GCLOUD_ERROR_REPORTING: 1
```

Also you must add it to your deployment configuration. For example, if you are using Google App Engine, you must add it
 to your config file:

```yaml
# app.yaml
env_variables:
    GCLOUD_ERROR_REPORTING: 1
```
 
## Google Cloud permissions

Important: you require an active Service account with **Error Reporting Writer** permission.

