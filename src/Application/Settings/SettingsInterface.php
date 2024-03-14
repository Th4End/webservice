<?php

declare(strict_types=1);


namespace App\Application\Settings;
use Psr\Container\ContainerInterface;
interface SettingsInterface
{
    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key = '');
    //public function post(string $key = '');
}
