<?php

namespace App\Tests\Bootstrap;

use App\Kernel;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use App\Http\Mock\HttpClientMock;
use Psr\Http\Message\RequestInterface;

class CmiApiContext implements Context
{


    private const VIDEO_API_BASE_URL = 'http://api-video';
    private const VIDEO_API_REQUEST_CONTENT_TYPE = 'application/json';
    private const VIDEO_API_AUTH_URL = self::VIDEO_API_BASE_URL.'/v1/auth';
    private const VIDEO_API_AUTH_METHOD = 'POST';
    private const VIDEO_API_AUTH_CONTENT_TYPE = self::VIDEO_API_REQUEST_CONTENT_TYPE;
    private const VIDEO_API_AUTH_BODY = ['username' => 'user', 'password' => 'pass'];

    /**
     * @var Kernel
     */
    private $kernel;

    /**
     * @var HttpClientMock
     */
    private $httpClientMock;

    /**
     * @var int
     */
    private $requestIndex = 0;

    public function __construct(Kernel $kernel, HttpClientMock $httpClientMock)
    {
        $this->kernel = $kernel;
        $this->httpClientMock = $httpClientMock;
    }


    /**
     * @BeforeScenario
     */
    public function resetHttpCalls()
    {
        $this->httpClientMock->reset();
    }

    /**
     * @Given the next call to the video API will be an authentication
     */
    public function theNextCallToTheVideoApiWillBeAnAuthentication()
    {
        $this->httpClientMock->configureNextResponse(
            200,
            'http://api.google',
            'POST',
            ['headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json']],
            json_encode([
                'token' => 'aaa',
                'tokenDuration' => 3600,
                'tokenExpirationDate' => '2079-06-14T11:42:07+02:00'
            ])
        );
    }
}
