<?php

namespace App\Logger;

use Psr\Log\LoggerInterface;

class ContextualizedLogger implements LoggerInterface
{
    /**
     * @var LoggerInterface
     */
    private $decoratedLogger;

    /**
     * @var array
     */
    private $context = [];

    public function __construct(LoggerInterface $decoratedLogger)
    {
        $this->decoratedLogger = $decoratedLogger;
    }

    public function addToContext(string $key, $value): void
    {
        $this->context[$key] = $value;
    }

    public function emergency($message, array $context = array())
    {
        $this->decoratedLogger->emergency($message, $this->mergeContexts($context));
    }

    private function mergeContexts(array $contextToMerge)
    {
        return array_merge($contextToMerge, $this->context);
    }

    public function alert($message, array $context = array())
    {
        $this->decoratedLogger->alert($message, $this->mergeContexts($context));
    }

    public function critical($message, array $context = array())
    {
        $this->decoratedLogger->critical($message, $this->mergeContexts($context));
    }

    public function error($message, array $context = array())
    {
        $this->decoratedLogger->error($message, $this->mergeContexts($context));
    }

    public function warning($message, array $context = array())
    {
        $this->decoratedLogger->warning($message, $this->mergeContexts($context));
    }

    public function notice($message, array $context = array())
    {
        $this->decoratedLogger->notice($message, $this->mergeContexts($context));
    }

    public function info($message, array $context = array())
    {
        $this->decoratedLogger->info($message, $this->mergeContexts($context));
    }

    public function debug($message, array $context = array())
    {
        $this->decoratedLogger->debug($message, $this->mergeContexts($context));
    }

    public function log($level, $message, array $context = array())
    {
        $this->decoratedLogger->log($level, $message, $this->mergeContexts($context));
    }
}
