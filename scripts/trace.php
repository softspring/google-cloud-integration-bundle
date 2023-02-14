<?php

use Doctrine\DBAL\Connection;
use OpenCensus\Trace\Exporter\StackdriverExporter;
use OpenCensus\Trace\Tracer;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernel;
use Twig\Environment;

if (1 === (int) getenv('GCLOUD_TRACE') && extension_loaded('opencensus')) {
    opencensus_trace_method(HttpKernel::class, 'handle', function (HttpKernel $kernel, Request $request) {
        return [
            'name' => 'kernel/handle',
        ];
    });
    opencensus_trace_method(EventDispatcher::class, 'dispatch', function (EventDispatcher $dispatcher, $event, string $eventName) {
        return [
            'name' => $eventName,
            'attributes' => ['eventName' => $eventName],
        ];
    });
    opencensus_trace_method(Environment::class, 'render', function (Environment $environment, string $name, array $context = []) {
        return [
            'name' => 'twig.render',
            'attributes' => ['template' => $name],
        ];
    });
    opencensus_trace_method(Connection::class, 'executeStatement', function (Connection $connection, $sql, array $params = [], array $types = []) {
        return [
            'name' => 'doctrine.executeStatement',
            'attributes' => ['sql' => $sql],
        ];
    });
    opencensus_trace_method(Connection::class, 'exec', function (Connection $connection, $sql) {
        return [
            'name' => 'doctrine.exec',
            'attributes' => ['sql' => $sql],
        ];
    });
    opencensus_trace_method(Connection::class, 'executeQuery', function (Connection $connection, $sql, array $params = [], $types = []) {
        return [
            'name' => 'doctrine.exec',
            'attributes' => ['sql' => $sql],
        ];
    });
    opencensus_trace_method(Connection::class, 'beginTransaction', function (Connection $connection) {
        return [
            'name' => 'doctrine.beginTransaction',
        ];
    });
    opencensus_trace_method(Connection::class, 'commit', function (Connection $connection) {
        return [
            'name' => 'doctrine.commit',
        ];
    });
    opencensus_trace_method(Connection::class, 'rollBack', function (Connection $connection) {
        return [
            'name' => 'doctrine.rollBack',
        ];
    });
    opencensus_trace_method(Connection::class, 'query', function (Connection $connection) {
        return [
            'name' => 'doctrine.query',
        ];
    });

    Tracer::start(new StackdriverExporter());
}
