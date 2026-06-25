<?php

declare(strict_types=1);

namespace Softspring\Component\GoogleCloudIntegrationBundle\Tests\Logging;

use DateTimeImmutable;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;
use Softspring\Component\GoogleCloudIntegrationBundle\Logging\CloudLoggingProcessor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CloudLoggingProcessorTest extends TestCase
{
    public function testAddsGoogleCloudTraceOptionsFromRequestHeader(): void
    {
        $request = Request::create(
            'https://softspring.local/es/contact?source=test',
            'POST',
            server: [
                'CONTENT_LENGTH' => '123',
                'HTTP_USER_AGENT' => 'phpunit',
                'HTTP_X_CLOUD_TRACE_CONTEXT' => '105445aa7843bc8bf206b12000100000/123456789;o=1',
                'HTTP_X_REQUEST_ID' => 'request-123',
                'SERVER_PROTOCOL' => 'HTTP/2',
            ],
        );

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $processor = new CloudLoggingProcessor($requestStack, 'softspring-186011');
        $record = $processor(new LogRecord(
            datetime: new DateTimeImmutable(),
            channel: 'app',
            level: Level::Info,
            message: 'Request processed',
        ));

        $options = $record->context['stackdriverOptions'];

        $this->assertSame('projects/softspring-186011/traces/105445aa7843bc8bf206b12000100000', $options['trace']);
        $this->assertSame('00000000075bcd15', $options['spanId']);
        $this->assertTrue($options['traceSampled']);
        $this->assertSame('POST', $options['httpRequest']['requestMethod']);
        $this->assertSame('https://softspring.local/es/contact?source=test', $options['httpRequest']['requestUrl']);
        $this->assertSame(123, $options['httpRequest']['requestSize']);
        $this->assertSame('request-123', $options['labels']['request_id']);
    }
}
