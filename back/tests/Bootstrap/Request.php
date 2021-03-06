<?php

namespace App\Tests\Bootstrap;

use Behat\Mink\Mink;

class Request
{
    /**
     * @var Mink
     */
    private $mink;
    private $client;

    /**
     * Request constructor.
     * @param Mink $mink
     */
    public function __construct(Mink $mink)
    {
        $this->mink = $mink;
    }

    /**
     * @param string $name
     * @param mixed $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->getClient(), $name], $arguments);
    }

    /**
     * @return Request\BrowserKit
     */
    private function getClient()
    {
        if (null === $this->client) {
            $this->client = new Request\BrowserKit($this->mink);

        }

        return $this->client;
    }
}
