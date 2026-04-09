<?php

namespace Hristijans\AiStager\Responses;

use Laravel\Ai\Responses\AudioResponse;
use Laravel\Ai\Responses\Data\Meta;

/**
 * AudioResponse for the stager driver.
 *
 * Returns a placeholder MP3. In Phase 8 the placeholder is replaced by a
 * proper silent MP3 asset from resources/fixtures/audio/placeholder.mp3.
 * Until then an empty audio payload is used.
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

    /**
     * Return base64 from the real placeholder file when available, otherwise
     * return an empty payload.
     */
    private static function placeholderBase64(): string
    {
        $path = __DIR__.'/../../resources/fixtures/audio/placeholder.mp3';

        if (is_file($path)) {
            return base64_encode((string) file_get_contents($path));
        }

        return '';
    }
}
