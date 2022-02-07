<?php

namespace App\Http;


use App\RequestLogger\RequestLoggerInterface;
use App\RequestLogger\RequestLogSanitizerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class HttpRequester
{
    private const HTTP_INTERNAL_SERVER_ERROR = 500;
    private const HTTP_SERVICE_UNAVAILABLE = 503;

    /**
     * @var HttpClientInterface
     */
    private $client;

    /**
     * @var RequestLoggerInterface
     */
    private $requestLogger;

    public function __construct(HttpClientInterface $client, LoggerInterface $requestLogger)
    {
        $this->client = $client;
        $this->requestLogger = $requestLogger;
    }

    public function createAndSendRequest(
        string $targetName,
        string $requestMethod,
        string $requestUrl,
        ?string $requestBody = null,
        array $headers = []
    ): ResponseInterface
    {
        $request = [
            'url' => $requestUrl,
            'method' => $requestMethod,
            'options' => [
                'headers' => $headers,
                'body' => $requestBody
                ]
            ];

        return $this->sendRequest($targetName, $request);
    }

    private function sendRequest(
        string $targetName,
        array $request
    ) {
        try {
            $response = $this->client->request(
                        $request['method'],
                        $request['url'],
                        $request['options'],
            );
        } catch (\Exception $exception) {
            $this->throwNewException('Did not received any response', self::HTTP_SERVICE_UNAVAILABLE);
        }

        return $response;
    }

    private function isTimeoutErrorException(GuzzleException $exception)
    {
        if (!($exception instanceof ConnectException)) {
            return false;
        }

        $context = $exception->getHandlerContext();
        $errno = $context['errno'] ?? null;

        return 28 === $errno;
    }

    /**
     * @return mixed
     */
    private function getOption(string $optionName, array $options, $defaultValue)
    {
        return array_key_exists($optionName, $options) ? $options[$optionName] : $defaultValue;
    }

    private function throwExceptionAndLogIfIsErrorResponse(
        string $targetName,
        ResponseInterface $response,
        RequestInterface $associatedRequest,
        float $executionTime,
        array $ignoreErrorStatusCodes = [],
        bool $ignoreErrors,
        RequestLogSanitizerInterface $logSanitizer = null
    ) {
        $statusCode = $response->getStatusCode();
        $isStatusCodeIgnored = in_array($statusCode, $ignoreErrorStatusCodes);

        if ($this->isErrorResponse($response) && !$isStatusCodeIgnored && !$ignoreErrors) {
            $this->logRequestError($targetName, 'Error response received', $associatedRequest, $response, $executionTime, $logSanitizer);
            $errorMessage = sprintf('Got a response with a %s %s error', $statusCode, $response->getReasonPhrase());
            $this->throwNewException($errorMessage, $statusCode);
        }
    }

    private function isErrorResponse(ResponseInterface $response)
    {
        return $response->getStatusCode() >= 400;
    }

    /**
     * @throws HttpException
     */
    private function throwNewException(
        string $errorMessage,
        int $statusCode
    ) {
        $exception = new HttpException($statusCode, $errorMessage);
        $exception->markAsLogged();

        throw $exception;
    }

    private function logRequest(
        string $targetName,
        RequestInterface $request,
        ResponseInterface $response,
        float $executionTime,
        RequestLogSanitizerInterface $logSanitizer = null
    ) {
        $logTitle = $this->getLogTitle($targetName);
        $this->requestLogger->logRequest($logTitle, $request, $response, $executionTime, $logSanitizer);
    }

    private function logRequestError(
        string $targetName,
        string $reason,
        RequestInterface $request,
        ?ResponseInterface $response = null,
        float $executionTime = null,
        RequestLogSanitizerInterface $logSanitizer = null,
        bool $logAsDebug = false
    ) {
        $logTitle = $this->getLogTitle($targetName);
        $this->requestLogger->logRequestError($logTitle, $reason, $request, $response, $executionTime, $logSanitizer, $logAsDebug);
    }

    private function getLogTitle(string $targetName)
    {
        return sprintf('%s call.', $targetName);
    }
}
