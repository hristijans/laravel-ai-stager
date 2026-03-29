<?php

namespace Hristijans\AiStager\Responses;

use Illuminate\Support\Collection;
use Laravel\Ai\Responses\Data\GeneratedImage;
use Laravel\Ai\Responses\Data\Meta;
use Laravel\Ai\Responses\Data\Usage;
use Laravel\Ai\Responses\ImageResponse;

/**
 * ImageResponse for the stager driver.
 *
 * Returns a placeholder PNG image. In Phase 8 the placeholder is replaced by
 * a proper 512×512 PNG asset from resources/fixtures/images/placeholder.png.
 * Until then a minimal 1×1 transparent PNG is embedded as a base64 constant.
 */
class StagerImageResponse extends ImageResponse
{
    /**
     * Minimal valid 1×1 transparent PNG encoded as base64.
     * Replaced by the real placeholder asset once Phase 8 lands.
     */
    private const PLACEHOLDER_PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

    /**
     * Build a stager image response wrapping the placeholder PNG.
     */
    public static function make(): self
    {
        $base64 = self::placeholderBase64();

        return new self(
            images: new Collection([new GeneratedImage($base64, 'image/png')]),
            usage: new Usage,
            meta: new Meta(provider: 'stager', model: 'stager'),
        );
    }

    /**
     * Return base64 from the real placeholder file when available, otherwise
     * fall back to the embedded constant.
     */
    private static function placeholderBase64(): string
    {
        $path = __DIR__.'/../../resources/fixtures/images/placeholder.png';

        if (is_file($path)) {
            return base64_encode((string) file_get_contents($path));
        }

        return self::PLACEHOLDER_PNG;
    }
}
