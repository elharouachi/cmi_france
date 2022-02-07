<?php

namespace App\RequestLogger;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class RequestLogger implements RequestLoggerInterface
{
    private const CONTENT_TYPE_JSON = 'application/json';
    private const LOGGABLE_JSON_STRING_SIZE = 300;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function logRequest(
        string $title,
        RequestInterface $request,
        ResponseInterface $response,
        float $executionTime,
        RequestLogSanitizerInterface $requestLogSanitizer = null
    ) {
        $sanitizedValues = $this->getSanitizedValues($request, $response, $requestLogSanitizer);
        $logMessage = <<<MESSAGE
$title

Execution time: %s s
Status code: %s %s
Request method: %s
Request URL: %s
Request headers:%s
Request body: %s
Response body: %s

MESSAGE;
        $logMessage = sprintf(
            $logMessage,
            $executionTime,
            $response->getStatusCode(),
            $response->getReasonPhrase(),
            $request->getMethod(),
            $sanitizedValues['url'],
            $this->headersArrayToString($sanitizedValues['requestHeaders']),
            $sanitizedValues['requestBody'],
            $sanitizedValues['responseBody']
        );

        $this->logger->debug($logMessage, ['execution_time' => $executionTime]);
    }

    public function logRequestError(
        string $title,
        string $reason,
        RequestInterface $request,
        ?ResponseInterface $response = null,
        ?float $executionTime = null,
        RequestLogSanitizerInterface $requestLogSanitizer = null,
        bool $logAsDebug = false
    ) {
        $sanitizedValues = $this->getSanitizedValues($request, $response, $requestLogSanitizer);
        $errorMessage = <<<MESSAGE
$title

Reason: %s
Execution time: %s
Status code: %s %s
Request method: %s
Request URL: %s
Request headers:%s
Request body: %s
Response body: %s

MESSAGE;
        $errorMessage = sprintf(
            $errorMessage,
            $reason,
            $executionTime ? sprintf('%s s', $executionTime) : 'Unknown',
            $response ? $response->getStatusCode() : 'None',
            $response ? $response->getReasonPhrase() : '',
            $request->getMethod(),
            $sanitizedValues['url'],
            $this->headersArrayToString($sanitizedValues['requestHeaders']),
            $sanitizedValues['requestBody'],
            $sanitizedValues['responseBody'] ? $sanitizedValues['responseBody'] : 'None'
        );

        if ($logAsDebug) {
            $this->logger->debug($errorMessage, ['execution_time' => $executionTime]);
        } else {
            $this->logger->log($this->getErrorLogLevel($response), $errorMessage, ['execution_time' => $executionTime]);
        }
    }

    private function headersArrayToString(array $headerList): string
    {
        $headerString = "";

        foreach ($headerList as $headerName => $headerValues) {
            foreach ($headerValues as $headerValue) {
                // truncate bearer tokens as they might be very long
                if ('Authorization' === $headerName && 0 === strpos($headerValue, 'Bearer ')) {
                    $tokenBeginning = substr($headerValue, 7, 10);
                    $headerValue = sprintf('Bearer %s ...', $tokenBeginning);
                }

                $headerString .= "\n    $headerName: $headerValue";
            }
        }

        return $headerString;
    }

    private function prettifyBody(MessageInterface $message)
    {
        $body = $message->getBody();
        $body->rewind();
        $bodyContent = $body->getContents();

        if ([self::CONTENT_TYPE_JSON] === $message->getHeader('Content-Type')) {
            return $this->getLoggableJsonString($bodyContent);
        }

        return $bodyContent;
    }

    private function getLoggableJsonString($jsonString)
    {
        $jsonString = $this->jsonStringToJsonPrettyPrint($jsonString);
        preg_match_all(
            sprintf('#"[^"]+":\s*"(.{%s,})\\\\{0}"#m', self::LOGGABLE_JSON_STRING_SIZE),
            $jsonString,
            $matches
        );

        foreach ($matches[1] as $match) {
            $jsonString = str_replace(
                $match,
                sprintf(
                    '%s... (%s characters)',
                    substr($match, 0, self::LOGGABLE_JSON_STRING_SIZE),
                    strlen($match)
                ),
                $jsonString
            );
        }

        return $jsonString;
    }

    private function jsonStringToJsonPrettyPrint(?string $jsonString): ?string
    {
        if (empty($jsonString)) {
            return null;
        }

        $decodedJson = json_decode($jsonString);

        if (null === $decodedJson) {
            return $jsonString;
        } else {
            return json_encode($decodedJson, JSON_PRETTY_PRINT);
        }
    }

    private function getErrorLogLevel(ResponseInterface $response = null): string
    {
        if (null == $response) {
            return LogLevel::CRITICAL;
        } elseif ($response->getStatusCode() >= 500) {
            return LogLevel::CRITICAL;
        } else {
            return LogLevel::ERROR;
        }
    }

    private function getSanitizedValues(RequestInterface $request, ResponseInterface $response = null, RequestLogSanitizerInterface $requestLogSanitizer = null): array
    {
        $values = [
            'url' => (string)$request->getUri(),
            'requestHeaders' => $request->getHeaders(),
            'requestBody' => $this->prettifyBody($request),
            'responseBody' => $response ? $this->prettifyBody($response) : null,
        ];

        if (null === $requestLogSanitizer) {
            return $values;
        }

        $values['url'] = $requestLogSanitizer->sanitizeUrl($request->getUri());
        $values['requestHeaders'] = $requestLogSanitizer->sanitizeHeaders($request->getHeaders());
        $values['requestBody'] = $requestLogSanitizer->sanitizeRequestBody($values['requestBody']);
        $values['responseBody'] = $requestLogSanitizer->sanitizeResponseBody($values['responseBody']);

        return $values;
    }
}
