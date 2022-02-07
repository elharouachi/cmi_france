<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/../vendor/autoload.php';

if (file_exists(dirname(__DIR__).'/../config/bootstrap.php')) {
    require dirname(__DIR__).'/../config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    shell_exec(
        sprintf('rm -rf %s/../var/cache/test', dirname(__DIR__))
    );

    (new Dotenv())->bootEnv(dirname(__DIR__).'/../.env');
}
