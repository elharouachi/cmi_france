<?php

namespace App\RequestLogger;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class NullRequestLogger implements RequestLoggerInterface
{
    public function logRequest(
        string $title,
        RequestInterface $request,
        ResponseInterface $response,
        float $executionTime,
        RequestLogSanitizerInterface $requestLogSanitizer = null
    ) {
    }

    public function logRequestError(
        string $title,
        string $reason,
        RequestInterface $request,
        ?ResponseInterface $response = null,
        ?float $executionTime = null,
        RequestLogSanitizerInterface $requestLogSanitizer = null
    ) {
    }
}
