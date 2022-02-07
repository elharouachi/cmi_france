<?php

namespace App\Tests\Bootstrap;

use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ExpectationException;
use App\Tests\Bootstrap\Request;

class RestContext extends BaseContext
{
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var \Behat\Behat\Context\Context
     */
    private $jsonContext;
    private $entityManager;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Gives access to the RestContext.
     *
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        /** @var InitializedContextEnvironment $environment */
        $environment = $scope->getEnvironment();
        $this->jsonContext = $environment->getContext(JsonExtendedContext::class);
        /** @var FeatureContext $featureContext */
        $featureContext = $environment->getContext(FeatureContext::class);
        $this->entityManager = $featureContext->getKernel()->getContainer()->get('doctrine.orm.entity_manager');
    }

    /**
     * Sends a HTTP request with a body
     *
     * @Given I send a :method request to :url with body:
     */
    public function iSendARequestToWithBody($method, $url, PyStringNode $body)
    {
        return $this->iSendARequestTo($method, $url, $body);
    }


    /**
     * Sends a HTTP request
     *
     * @Given I send a :method request to :url
     */
    public function iSendARequestTo($method, $url, PyStringNode $body = null, $files = [])
    {
        return $this->request->send(
            $method,
            $this->locatePath($url),
            [],
            $files,
            $body !== null ? $body->getRaw() : null
        );
    }

    /**
     * @When I send a :method request to the API on :url with body:
     */
    public function iSendARequestToTheApiOnWithBody($method, $url, PyStringNode $body)
    {

        /* @phpstan-ignore-next-line */
        $this->request->setHttpHeader('Accept', 'application/json');
        /* @phpstan-ignore-next-line */
        $this->request->setHttpHeader('Content-Type', 'application/json');

        return $this->iSendARequestToWithBody($method, $url, $body);
    }


    /**
     * @Then I should see that the entity was created with response body:
     */
    public function iShouldSeeThatTheEntityWasCreatedWithResponseBody(PyStringNode $body)
    {
        $this->assertLastResponseStatusCode(201);
        $this->theHeaderShouldBeEqualTo('Content-Type', 'application/json; charset=utf-8');
        $this->jsonContext->theJsonShouldBeEqualTo($body);
    }



    private function assertLastResponseStatusCode($expectedCode)
    {
        $this->assertSession()->statusCodeEquals($expectedCode);
    }

    /**
     * Checks, whether the header name is equal to given text
     *
     * @Then the header :name should be equal to :value
     */
    public function theHeaderShouldBeEqualTo($name, $value)
    {
        $actual = $this->request->getHttpHeader($name);
        $this->assertEquals(strtolower($value), strtolower($actual),
            "The header '$name' should be equal to '$value', but it is: '$actual'"
        );
    }
}
