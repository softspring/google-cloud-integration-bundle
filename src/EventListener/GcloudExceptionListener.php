<?php

namespace Softspring\Component\GoogleCloudIntegrationBundle\EventListener;

use Google\Cloud\ErrorReporting\Bootstrap;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class GcloudExceptionListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => [
            ['logException', 5],
        ]];
    }

    public function logException(ExceptionEvent $event): void
    {
        $statusCode = $event->getResponse() ? $event->getResponse()->getStatusCode() : 0;
        if (400 <= $statusCode && $statusCode <= 499) {
            // do not track client common errors
            return;
        }

        Bootstrap::exceptionHandler($event->getThrowable());
    }
}
