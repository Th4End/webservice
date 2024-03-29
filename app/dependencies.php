<?php

declare(strict_types=1);

use App\Application\Settings\SettingsInterface;
use App\Application\Actions\Settings;
use DI\Container;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);

            $loggerSettings = $settings->get('logger');
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },
        PDO::class => function (ContainerInterface $c){
            $settings = $c->get(SettingsInterface::class);
            $dbsettings = $settings->get('db');
            $host = $dbsettings['host'];
            $database = $dbsettings['database'];
            $user = $dbsettings['user'];
            $password = $dbsettings['pass'];
            $connection= "mysql:host = $host;dbname=$database";
            return new PDO($connection, $user,$password);
        }
    ]);
};
