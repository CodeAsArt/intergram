<?php

use Intergram\Parser\ServiceParserProvider;
use Intergram\Logger\ServiceLoggerProvider;

ini_set('display_errors', 1);

/** @var Silex\Application $app */
$app['debug'] = true;
$app['intergram.uselogger'] = true;

/* Register DBAL */
$app->register(new Silex\Provider\DoctrineServiceProvider(), [
    'db.options' => [
        'driver'   => 'mysql',
        'host' => '127.0.0.1',
        'dbname' => 'workx_intergram_app',
        'user' => 'root',
        'password' => '',
    ],
]);

/* Register Parser */
$app->register(new ServiceParserProvider());

/* Register Logger */
$app->register(new ServiceLoggerProvider(), [
    'intergram.logger.type' => ServiceLoggerProvider::LOGGER_BASIC,
    'intergram.logger.conf' => [
        'inlinePrint' => false,
        'elementsOrder' => [],
    ]
]);
