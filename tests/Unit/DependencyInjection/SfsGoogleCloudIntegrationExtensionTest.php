<?php

declare(strict_types=1);

namespace Softspring\Component\GoogleCloudIntegrationBundle\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Softspring\Component\GoogleCloudIntegrationBundle\DependencyInjection\SfsGoogleCloudIntegrationExtension;
use Softspring\Component\GoogleCloudIntegrationBundle\EventListener\GcloudExceptionListener;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SfsGoogleCloudIntegrationExtensionTest extends TestCase
{
    protected function tearDown(): void
    {
        putenv('GCLOUD_ERROR_REPORTING');
    }

    public function testDoesNotLoadExceptionListenerWhenErrorReportingIsDisabled(): void
    {
        putenv('GCLOUD_ERROR_REPORTING=0');
        $container = new ContainerBuilder();

        (new SfsGoogleCloudIntegrationExtension())->load([], $container);

        self::assertFalse($container->hasDefinition(GcloudExceptionListener::class));
    }

    public function testLoadsExceptionListenerWhenErrorReportingIsEnabled(): void
    {
        putenv('GCLOUD_ERROR_REPORTING=1');
        $container = new ContainerBuilder();

        (new SfsGoogleCloudIntegrationExtension())->load([], $container);

        self::assertTrue($container->hasDefinition(GcloudExceptionListener::class));
    }
}
