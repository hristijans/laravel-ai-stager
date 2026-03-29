<?php

namespace Hristijans\AiStager\Responses;

use Laravel\Ai\Responses\Data\Meta;
use Laravel\Ai\Responses\EmbeddingsResponse;

/**
 * EmbeddingsResponse for the stager driver.
 *
 * Generates zero vectors by default. Phase 5 wires in HashVector for
 * deterministic non-zero vectors when the hash embedding strategy is active.
 */
class StagerEmbeddingsResponse extends EmbeddingsResponse
{
    /**
     * Build a stager embeddings response.
     *
     * @param  string[]  $inputs
     * @param  array<int, array<float>>|null  $vectors  Pre-built vectors; zero vectors generated when null.
     */
    public static function make(array $inputs, int $dimensions, ?array $vectors = null): self
    {
        $embeddings = $vectors ?? array_map(
            fn () => array_fill(0, $dimensions, 0.0),
            $inputs,
        );

        return new self(
            embeddings: $embeddings,
            tokens: count($inputs),
            meta: new Meta(provider: 'stager', model: 'stager'),
        );
    }
}
