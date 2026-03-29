<?php

namespace Hristijans\AiStager\Responses;

use Illuminate\Support\Collection;
use Laravel\Ai\Responses\Data\Meta;
use Laravel\Ai\Responses\Data\Usage;
use Laravel\Ai\Responses\TranscriptionResponse;

/**
 * TranscriptionResponse for the stager driver.
 */
class StagerTranscriptionResponse extends TranscriptionResponse
{
    /**
     * Build a stager transcription response for the given text.
     */
    public static function make(string $text): self
    {
        return new self(
            text: $text,
            segments: new Collection,
            usage: new Usage,
            meta: new Meta(provider: 'stager', model: 'stager'),
        );
    }
}
