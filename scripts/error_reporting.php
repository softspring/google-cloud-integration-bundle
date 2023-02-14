<?php

use Google\Cloud\ErrorReporting\Bootstrap;

(1 === (int) getenv('GCLOUD_ERROR_REPORTING')) && Bootstrap::init();
