<?php

declare(strict_types=1);

namespace Softspring\Component\GoogleCloudIntegrationBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class SfsGoogleCloudIntegrationBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
