<?php

declare(strict_types=1);

namespace Softspring\Component\GoogleCloudIntegrationBundle\Tests\Unit\EventListener;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Softspring\Component\GoogleCloudIntegrationBundle\EventListener\GcloudExceptionListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class GcloudExceptionListenerTest extends TestCase
{
    public function testSubscribesToKernelException(): void
    {
        self::assertSame([
            KernelEvents::EXCEPTION => [
                ['logException', 5],
            ],
        ], GcloudExceptionListener::getSubscribedEvents());
    }

    public function testIgnoresClientErrorResponses(): void
    {
        $event = new ExceptionEvent(
            $this->createStub(HttpKernelInterface::class),
            Request::create('/missing'),
            HttpKernelInterface::MAIN_REQUEST,
            new RuntimeException('Not found')
        );
        $event->setResponse(new Response('', 404));

        (new GcloudExceptionListener())->logException($event);

        self::assertSame(404, $event->getResponse()?->getStatusCode());
    }
}
