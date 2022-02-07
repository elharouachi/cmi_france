<?php

namespace App\Http;

use App\Http\JsonApiRequester;

class CmiApiRequester
{
    private const API_NAME = 'CMI API';

    /**
     * @var \App\Http\JsonApiRequester
     */
    private $apiRequester;

    /**
     * @var string
     */
    private $apiUrl;

    /**
     * @var string
     */
    private $apiDomain;

    public function __construct(JsonApiRequester $apiRequester, string $apiUrl, ?string $apiDomain)
    {
        $this->apiRequester = $apiRequester;
        $this->apiUrl = rtrim($apiUrl, '/');
        $this->apiDomain = $apiDomain;
    }

    /**
     * @param array $options see HttpRequester::createAndSendRequest
     */
    public function request(string $method, string $url, ?array $data = null, array $options = []): ?array
    {
        $headers = [
            'X-Disable-Cache' => 'true',
            'X-No-Data-Delay' => 'true',
        ];

        $url = sprintf(
            '%s/%s',
            $this->apiUrl,
            ltrim($url, '/')
        );

        $response = $this->apiRequester->createAndSendRequest(
            self::API_NAME,
            $method,
            $url,
            $data,
            $headers,
            $options
        );


        $responseBody = $response->getContent();

        return $responseBody ? json_decode($responseBody, true) : null;
    }

    /**
     * @param array $options see HttpRequester::createAndSendRequest
     */
    public function getEntities(string $entityName, ?int $limit = 10, int $page = 1, array $options = []): ?array
    {
        $url = $this->getEntityUrl($entityName);
        $url = sprintf('%s?page=%s', $url, $page);

        if ($limit) {
            $url .= '&limit='.$limit;
        }

        return $this->request('GET', $url, null, $options);
    }

    /**
     * @param array $options see HttpRequester::createAndSendRequest
     */
    public function getEntity(string $entityName, string $entityId, array $options = []): ?array
    {
        $this->validateEntityId($entityId);
        $url = $this->getEntityUrl($entityName, $entityId);

        return $this->request('GET', $url, null, $options);
    }

    /**
     * @param array $options see HttpRequester::createAndSendRequest
     */
    public function createEntity(string $entityName, array $data, array $options = []): ?array
    {
        $url = $this->getEntityUrl($entityName);

        return $this->request('POST', $url, $data, $options);
    }

    /**
     * @param array $options see HttpRequester::createAndSendRequest
     */
    public function updateEntity(string $entityName, string $entityId, array $data, array $options = []): ?array
    {
        $this->validateEntityId($entityId);
        $url = $this->getEntityUrl($entityName, $entityId);
        return $this->request('PUT', $url, $data, $options);
    }

    /**
     * @param array $options see HttpRequester::createAndSendRequest
     */
    public function deleteEntity(string $entityName, string $entityId, array $options = []): void
    {
        $this->validateEntityId($entityId);
        $url = $this->getEntityUrl($entityName, $entityId);

        $this->request('DELETE', $url, null, $options);
    }


    private function getEntityUrl(string $entityName, ?string $entityId = null): string
    {
        $this->validateEntityName($entityName);
        $url = sprintf('/%s', $entityName);

        if ($entityId) {
            $url .= sprintf('/%s', $entityId);
        }

        return $url;
    }

    private function validateEntityName(string $entityName)
    {
        if (empty($entityName)) {
            throw new \InvalidArgumentException('Entity name cannot be empty');
        }
    }

    private function validateEntityId(string $entityId)
    {
        if (empty($entityId)) {
            throw new \InvalidArgumentException('Entity id cannot be empty');
        }
    }
}
