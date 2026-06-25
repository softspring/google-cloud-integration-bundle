<?php

declare(strict_types=1);

namespace Softspring\Component\GoogleCloudIntegrationBundle\Logging;

use Monolog\LogRecord;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final readonly class CloudLoggingProcessor
{
    public function __construct(
        private RequestStack $requestStack,
        private string $gcloudProject,
    ) {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        $request = $this->requestStack->getMainRequest();

        if (!$request || '' === $this->gcloudProject) {
            return $record;
        }

        $context = $record->context;
        $options = $context['stackdriverOptions'] ?? [];
        $options['httpRequest'] = ($options['httpRequest'] ?? []) + $this->httpRequest($request);

        if ($traceOptions = $this->traceOptions($request)) {
            $options = $traceOptions + $options;
        }

        if ($requestId = $request->headers->get('X-Request-Id')) {
            $options['labels'] = ($options['labels'] ?? []) + [
                'request_id' => $requestId,
            ];
        }

        $context['stackdriverOptions'] = $options;

        return $record->with(context: $context);
    }

    /**
     * @return array<string, string|int>
     */
    private function httpRequest(Request $request): array
    {
        $httpRequest = [
            'requestMethod' => $request->getMethod(),
            'requestUrl' => $request->getUri(),
            'protocol' => $request->server->get('SERVER_PROTOCOL', 'HTTP/1.1'),
        ];

        if ($request->headers->has('User-Agent')) {
            $httpRequest['userAgent'] = $request->headers->get('User-Agent');
        }

        if ($request->headers->has('Referer')) {
            $httpRequest['referer'] = $request->headers->get('Referer');
        }

        if ($request->getClientIp()) {
            $httpRequest['remoteIp'] = $request->getClientIp();
        }

        if ($request->server->has('CONTENT_LENGTH')) {
            $httpRequest['requestSize'] = (int) $request->server->get('CONTENT_LENGTH');
        }

        return $httpRequest;
    }

    /**
     * @return array{trace: string, spanId?: string, traceSampled?: bool}|null
     */
    private function traceOptions(Request $request): ?array
    {
        $traceContext = $request->headers->get('X-Cloud-Trace-Context');

        if (!$traceContext || !preg_match('/^([a-fA-F0-9]{32})(?:\/([0-9]+))?(?:;o=(\d))?$/', $traceContext, $matches)) {
            return null;
        }

        $options = [
            'trace' => sprintf('projects/%s/traces/%s', $this->gcloudProject, $matches[1]),
        ];

        if (isset($matches[2]) && '' !== $matches[2]) {
            $options['spanId'] = $this->decimalSpanIdToHex($matches[2]);
        }

        if (isset($matches[3])) {
            $options['traceSampled'] = '1' === $matches[3];
        }

        return $options;
    }

    private function decimalSpanIdToHex(string $spanId): string
    {
        $spanId = ltrim($spanId, '0');

        if ('' === $spanId) {
            return str_repeat('0', 16);
        }

        $hex = '';
        $digits = array_map('intval', str_split($spanId));

        while ([] !== $digits) {
            $quotient = [];
            $remainder = 0;

            foreach ($digits as $digit) {
                $value = $remainder * 10 + $digit;
                $quotientDigit = intdiv($value, 16);
                $remainder = $value % 16;

                if ([] !== $quotient || 0 !== $quotientDigit) {
                    $quotient[] = $quotientDigit;
                }
            }

            $hex = dechex($remainder).$hex;
            $digits = $quotient;
        }

        return str_pad(substr($hex, -16), 16, '0', STR_PAD_LEFT);
    }
}
