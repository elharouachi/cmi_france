<?php

namespace App\Cmi;

use App\Http\CmiApiRequester;

class ArticleRetriever
{
    private $cmiApiRequester;

    public function __construct(CmiApiRequester $cmiApiRequester)
    {
        $this->cmiApiRequester = $cmiApiRequester;
    }

    /**
     * @throws \Exception
     */
    public function getArticle(string $articleId): ?array
    {
        $articleEntity = $this->cmiApiRequester->getEntity(
            'articles',
            $articleId,
            ['ignoreErrorStatusCodes' => [404]]
        );

        if (!array_key_exists('id', $articleEntity)) {
            return null;
        }

        return $articleEntity;
    }

    /**
     * @throws \Exception
     */
    public function getArticles(): ?array
    {
        $articleEntity = $this->cmiApiRequester->getEntities(
            'articles',
            10,
            1,
            ['ignoreErrorStatusCodes' => [404]]
        );

        return $articleEntity;
    }
}
