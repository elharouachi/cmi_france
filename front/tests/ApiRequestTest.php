<?php

namespace App\Tests;

use App\Http\CmiApiRequester;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use App\Http\JsonApiRequester;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class ApiRequestTest extends TestCase
{

    private const API_URL = 'http://api-cmi';
    private const API_DOMAIN = 'api-domain';
    /**
     * @var JsonApiRequester|MockObject
     */
    private $jsonApiRequester;
    /**
     * @var CmiApiRequester
     */
    private $cmiApiRequester;

    protected function setUp(): void
    {
        $this->jsonApiRequester = $this->getMockBuilder(JsonApiRequester::class)->disableOriginalConstructor()->getMock();
        $this->response = $this->getMockBuilder(ResponseInterface::class)->getMock();


        $this->cmiApiRequester = new CmiApiRequester(
            $this->jsonApiRequester,
            self::API_URL,
            self::API_DOMAIN
        );

    }

    public function testRequest()
    {
        $requestUrl = '/articles';
        $expectedResponseBody =  [
            [
                'id' => '1',
                'title' => 'title',
                'content' => 'description',
                'publishedAt' => '2022-02-05T17:35:25+01:00',
                'author' => 'author name',
                'image' => 'image name',
                'comments' => null
            ]
        ];
        $requestBody = [];
        $expectedRequestUrl = self::API_URL . $requestUrl;


        $this->jsonApiRequester
            ->expects($this->once())
            ->method('createAndSendRequest')
            ->with(
                'CMI API',
                'GET',
                $expectedRequestUrl,
                $requestBody,
                [
                    'X-Disable-Cache' => 'true',
                    'X-No-Data-Delay' => 'true',
                ],
                []
            )
            ->willReturn($this->response)
        ;

        $this->response
            ->expects($this->once())
            ->method('getContent')
            ->willReturn(json_encode($expectedResponseBody))
        ;

        $actualResponseBody = $this->cmiApiRequester->request('GET', $requestUrl, $requestBody);

        $this->assertSame($expectedResponseBody, $actualResponseBody);
    }

    public function testRequestWithOptions()
    {
        $requestUrl = '/articles';
        $requestBody = [];

        $this->jsonApiRequester
            ->expects($this->once())
            ->method('createAndSendRequest')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything(),
                ['e' => 'f']
            )
            ->willReturn($this->response)
        ;

        $this->cmiApiRequester->request('GET', $requestUrl, $requestBody, ['e' => 'f']);
    }

    public function testGetEntities()
    {
        $expectedUrl = self::API_URL.'/comments?page=1&limit=10';
        $expectedResponseBody = [
            [
                "id"=> 1,
                "authorName"=> "test 1",
                "note"=> 1,
                "content"=> "content comment 1",
                "article"=> "/api/articles/2"
            ]
        ];

        $this->jsonApiRequester
            ->expects($this->once())
            ->method('createAndSendRequest')
            ->with(
                'CMI API',
                'GET',
                $expectedUrl,
                null,
                [
                    'X-Disable-Cache' => 'true',
                    'X-No-Data-Delay' => 'true',
                ]
            )
            ->willReturn($this->response)
        ;

        $this->response
            ->expects($this->once())
            ->method('getContent')
            ->willReturn(json_encode($expectedResponseBody))
        ;

        $actualResponseBody = $this->cmiApiRequester->getEntities('comments');

        $this->assertSame($expectedResponseBody, $actualResponseBody);
    }

    public function testGetEntitiesWithEmptyEntityName()
    {
        $this->jsonApiRequester
            ->expects($this->never())
            ->method('createAndSendRequest')
        ;

        $this->expectException(\InvalidArgumentException::class);
        $this->cmiApiRequester->getEntities('');
    }

    public function testCreateEntity()
    {
        $expectedUrl = self::API_URL.'/comments';
        $expectedResponseBody = [
            [
                "authorName" => "test",
                "note" => 0,
                "content" => "test",
                "article" =>  "/api/articles/2"
            ]
        ];

        $requestBody = [
            "authorName" => "test",
            "note" => 0,
            "content" => "test",
            "article" => 2
        ];

        $this->jsonApiRequester
            ->expects($this->once())
            ->method('createAndSendRequest')
            ->with(
                'CMI API',
                'POST',
                $expectedUrl,
                $requestBody,
                [
                    'X-Disable-Cache' => 'true',
                    'X-No-Data-Delay' => 'true',
                ]
            )
            ->willReturn($this->response)
        ;

        $this->response
            ->expects($this->once())
            ->method('getContent')
            ->willReturn(json_encode($expectedResponseBody))
        ;

        $actualResponseBody = $this->cmiApiRequester->createEntity('comments', $requestBody);

        $this->assertSame($expectedResponseBody, $actualResponseBody);
    }
}
