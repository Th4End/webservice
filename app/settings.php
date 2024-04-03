<?php

declare(strict_types=1);

use App\Application\Settings\Settings;
use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {

    // Global Settings Object
    $containerBuilder->addDefinitions([
        SettingsInterface::class => function () {
            return new Settings([
                'displayErrorDetails' => true, // Should be set to false in production
                'logError'            => true,
                'logErrorDetails'     => true,
                'logger' => [
                    'name' => 'slim-app',
                    'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
                    'level' => Logger::DEBUG,
                ],
                'db' => [
                    'host' => '127.0.0.1',
                    'database' => 'ppe4_eliazid',
                    'user' => 'root',
                    'pass' => '',
                    'charset' => 'utf8',
                    'collation' => 'utf8_unicode_ci',
                    'flags' =>[
                        //désactiver les connections persistantes
                        PDO::ATTR_PERSISTENT => false,
                        // activer les exceptions
                        //PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
                        //Émuler les états préparés 
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ],
                ],
            ]);
        }
    ]);
};
