<?php

namespace App\Http\Mock;


use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

/**
 * @method  withOptions(array $options)
 */
class HttpClientMock extends MockHttpClient
{
    private static $data = [];

    public function configureNextResponse(int $statusCode, $url, $method,  ?array $options = [], ?string $body = null): void
    {
        self::$data['configuredResponses'][] =  MockResponse::fromRequest($method, $url, $options, $body);
    }
}
