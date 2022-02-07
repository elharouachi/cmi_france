<?php

namespace App\Http;

use Psr\Http\Message\RequestInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class JsonApiRequester
{
    /**
     * @var HttpRequester
     */
    private $httpRequester;

    public function __construct(HttpRequester $httpRequester)
    {
        $this->httpRequester = $httpRequester;
    }

    /**
     * @param array $options see HttpRequester::createAndSendRequest
     * @throws HttpException
     */
    public function createAndSendRequest(
        string $apiName,
        string $requestMethod,
        string $requestUrl,
        ?array $requestBody = null,
        array $headers = []

    ): ResponseInterface {
        $headers['Content-Type'] = "application/json";
        $headers['Accept'] = "application/json";


        return $this->httpRequester->createAndSendRequest(
            $this->getTargetName($apiName),
            $requestMethod,
            $requestUrl,
            $requestBody ? json_encode($requestBody) : null,
            $headers
        );
    }

    public function validateResponse(
        string $targetName,
        ResponseInterface $response,
        RequestInterface $associatedRequest
    ): ?string {
        $responseBody = $response->getBody();
        $responseBody->rewind();
        $responseBodyContent = $responseBody->getContents();

        if (empty($responseBodyContent)) {
            return null;
        }

        $decodedResponseBody = json_decode($responseBodyContent, true);

        if ($responseBodyContent && null === $decodedResponseBody) {
            return 'Malformed JSON body received';
        }

        return null;
    }

    private function getTargetName(string $apiName)
    {
        if (' API' !== substr($apiName, -4)) {
            $apiName = sprintf('%s API', $apiName);
        }

        return $apiName;
    }
}
