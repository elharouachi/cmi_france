<?php

namespace App\Tests\Bootstrap;

use Behat\Gherkin\Node\PyStringNode;
use Behatch\Context\BaseContext;

class SnippetContext extends BaseContext
{
    private $snippets = [];

    /**
     * @When I set the snippet :snippetName to:
     */
    public function iSetTheSnippetToBlock($snippetName, PyStringNode $snippet)
    {
        $this->setSnippet($snippetName, (string)$snippet);
    }

    /**
     * @When I set the snippet :snippetName to :snippet
     */
    public function iSetTheSnippetToString($snippetName, $snippet)
    {
        $this->setSnippet($snippetName, $snippet);
    }

    public function setSnippet(string $snippetName, string $snippet): void
    {
        $snippetName = preg_replace('#[^a-zA-Z0-9_.-]#', '', $snippetName);
        $this->snippets[$snippetName] = $snippet;
    }

    /**
     * @param PyStringNode|string $string
     */
    public function replaceSnippets($string): string
    {
        $replacedAtLeastOne = false;

        foreach ($this->snippets as $snippetName => $snippetContent) {
            $tag = sprintf('<snippet: %s>', $snippetName);
            $string = str_replace($tag, $snippetContent, $string, $count);
            $replacedAtLeastOne = $replacedAtLeastOne || $count > 0;
        }

        if ($replacedAtLeastOne && preg_match('#<snippet: .*>#U', $string)) {
            return $this->replaceSnippets($string);
        }

        return $string;
    }
}
