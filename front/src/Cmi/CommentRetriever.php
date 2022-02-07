<?php

namespace App\Cmi;

use App\Http\CmiApiRequester;

class CommentRetriever
{
    private $cmiApiRequester;

    public function __construct(CmiApiRequester $cmiApiRequester)
    {
        $this->cmiApiRequester = $cmiApiRequester;
    }

    /**
     * @throws \Exception
     */
    public function getComment(string $commentId): ?array
    {
        $commentEntity = $this->cmiApiRequester->getEntity(
            'comments',
            $commentId,
            ['ignoreErrorStatusCodes' => [404]]
        );

        if (!array_key_exists('id', $commentEntity)) {
            return null;
        }

        return $commentEntity;
    }
}
