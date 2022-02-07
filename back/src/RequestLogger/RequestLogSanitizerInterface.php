<?php

namespace App\RequestLogger;

use Psr\Http\Message\UriInterface;

interface RequestLogSanitizerInterface
{
    public function sanitizeHeaders(?array $headers): ?array;
    public function sanitizeRequestBody(?string $body): ?string;
    public function sanitizeResponseBody(?string $body): ?string;
    public function sanitizeUrl(?UriInterface $uri): ?string;
}
