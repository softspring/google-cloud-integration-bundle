<?php

declare(strict_types=1);

namespace Softspring\Component\GoogleCloudIntegrationBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Softspring\Component\GoogleCloudIntegrationBundle\SfsGoogleCloudIntegrationBundle;

class SfsGoogleCloudIntegrationBundleTest extends TestCase
{
    public function testReturnsPackagePath(): void
    {
        self::assertSame(dirname(__DIR__, 2), (new SfsGoogleCloudIntegrationBundle())->getPath());
    }
}
