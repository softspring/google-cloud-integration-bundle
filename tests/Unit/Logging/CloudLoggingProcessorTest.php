<?php

declare(strict_types=1);

namespace Softspring\Component\GoogleCloudIntegrationBundle\Tests\Logging;

use DateTimeImmutable;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Softspring\Component\GoogleCloudIntegrationBundle\Logging\CloudLoggingProcessor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CloudLoggingProcessorTest extends TestCase
{
    /**
     * @return iterable<string, array{?string}>
     */
    public static function missingProjectIds(): iterable
    {
        yield 'unset project' => [null];
        yield 'empty project' => [''];
    }

    #[DataProvider('missingProjectIds')]
    public function testPreservesTheRecordWhenNoProjectIsConfigured(?string $project): void
    {
        $requestStack = new RequestStack();
        $requestStack->push(Request::create('https://example.test/', server: [
            'HTTP_X_CLOUD_TRACE_CONTEXT' => '105445aa7843bc8bf206b12000100000/123;o=1',
        ]));
        $record = new LogRecord(
            datetime: new DateTimeImmutable(),
            channel: 'app',
            level: Level::Info,
            message: 'Local request',
            context: ['existing' => 'context'],
        );
        $processor = new CloudLoggingProcessor($requestStack, $project);

        self::assertSame($record, $processor($record));
    }

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
