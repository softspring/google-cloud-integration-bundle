<?php

namespace Softspring\Component\GoogleCloudIntegrationBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class SfsGoogleCloudIntegrationExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../config/services'));

        if (1 === (int) getenv('GCLOUD_ERROR_REPORTING')) {
            $loader->load('event_listener.yaml');
        }
    }
}
