<?php

namespace Hristijans\AiStager\Responses;

use Laravel\Ai\Responses\AudioResponse;
use Laravel\Ai\Responses\Data\Meta;

/**
 * AudioResponse for the stager driver.
 *
 * Returns a silent placeholder MP3 from resources/fixtures/audio/placeholder.mp3.
 */
class StagerAudioResponse extends AudioResponse
{
    /**
     * Build a stager audio response wrapping the placeholder MP3.
     */
    public static function make(): self
    {
        return new self(
            audio: self::placeholderBase64(),
            meta: new Meta(provider: 'stager', model: 'stager'),
            mime: 'audio/mpeg',
        );
    }

    private static function placeholderBase64(): string
    {
        $path = __DIR__.'/../../resources/fixtures/audio/placeholder.mp3';

        return is_file($path)
            ? base64_encode((string) file_get_contents($path))
            : '';
    }
}
