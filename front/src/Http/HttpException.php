<?php

namespace App\Http;

use Figaro\VideoServiceBundle\Logger\Loggable;
use Figaro\VideoServiceBundle\Logger\LoggableTrait;
use Symfony\Component\HttpKernel\Exception\HttpException as SymfonyHttpException;

class HttpException extends SymfonyHttpException implements Loggable
{
    use LoggableTrait;
}
