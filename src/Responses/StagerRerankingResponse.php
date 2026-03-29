<?php

namespace Hristijans\AiStager\Responses;

use Laravel\Ai\Responses\Data\Meta;
use Laravel\Ai\Responses\Data\RankedDocument;
use Laravel\Ai\Responses\RerankingResponse;

/**
 * RerankingResponse for the stager driver.
 *
 * Documents are returned in their original order with descending fake relevance
 * scores (1.0, 0.9, 0.8, …) capped at a minimum of 0.1. When a $limit is
 * supplied only the top-N results are included.
 */
class StagerRerankingResponse extends RerankingResponse
{
    /**
     * Build a stager reranking response for the given documents.
     *
     * @param  array<int, string>  $documents
     */
    public static function make(array $documents, ?int $limit = null): self
    {
        $results = [];
        $total = $limit !== null ? min($limit, count($documents)) : count($documents);

        for ($i = 0; $i < $total; $i++) {
            $score = max(0.1, round(1.0 - $i * 0.1, 1));

            $results[] = new RankedDocument(
                index: $i,
                document: $documents[$i],
                score: $score,
            );
        }

        return new self(
            results: $results,
            meta: new Meta(provider: 'stager', model: 'stager'),
        );
    }
}
