<?php

namespace App\Tests\Bootstrap;

use App\Kernel;
use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\MinkExtension\Context\MinkContext;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bridge\PhpUnit\ClockMock;

class FeatureContext extends MinkContext
{
    private const DOCTRINE_SERVICE_NAME = 'doctrine';

    private $kernel;
    private $elasticClient;

    private $manager;
    private $schemaTool;
    private $classes;


    /**
     * @var Registry
     */
    private $doctrine;

    public function __construct(
        Kernel $kernel
    ) {
        $this->kernel = $kernel;
        $this->kernel->shutdown();
        $this->kernel->boot();
        $this->doctrine = $this->kernel->getContainer()->get(self::DOCTRINE_SERVICE_NAME);

        $this->manager = $this->doctrine->getManager();
        $this->schemaTool = new SchemaTool($this->manager);
        $this->classes = $this->manager->getMetadataFactory()->getAllMetadata();

        date_default_timezone_set('UTC');

    }

    public function getKernel(): Kernel
    {
        return $this->kernel;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        /** @var InitializedContextEnvironment $environment */
        $environment = $scope->getEnvironment();
        $this->restContext = $environment->getContext(RestContext::class);
    }

    /**
     * @BeforeScenario
     */
    public function createDatabase()
    {
        $this->schemaTool->dropDatabase($this->classes);
        $this->manager->clear();
        $this->schemaTool->createSchema($this->classes);
    }

    /**
     * @Given the current time is :dateString
     */
    public function theCurrentTimeIs(string $dateString)
    {
        $dateTime = \DateTime::createFromFormat('Y-m-d\TH:i:sT', $dateString);
        $timestamp = $dateTime->format('U');

        ClockMock::withClockMock($timestamp);
    }
}
