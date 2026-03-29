<?php

namespace Hristijans\AiStager;

use BadMethodCallException;
use Laravel\Ai\Contracts\Files\TranscribableAudio;
use Laravel\Ai\Contracts\Gateway\AudioGateway;
use Laravel\Ai\Contracts\Gateway\EmbeddingGateway;
use Laravel\Ai\Contracts\Gateway\ImageGateway;
use Laravel\Ai\Contracts\Gateway\RerankingGateway;
use Laravel\Ai\Contracts\Gateway\TextGateway;
use Laravel\Ai\Contracts\Gateway\TranscriptionGateway;
use Laravel\Ai\Contracts\Providers\AudioProvider;
use Laravel\Ai\Contracts\Providers\EmbeddingProvider;
use Laravel\Ai\Contracts\Providers\ImageProvider;
use Laravel\Ai\Contracts\Providers\RerankingProvider;
use Laravel\Ai\Contracts\Providers\TextProvider;
use Laravel\Ai\Contracts\Providers\TranscriptionProvider;
use Laravel\Ai\Prompts\AgentPrompt;
use Laravel\Ai\Responses\AgentResponse;
use Laravel\Ai\Responses\AudioResponse;
use Laravel\Ai\Responses\Data\Meta;
use Laravel\Ai\Responses\Data\Usage;
use Laravel\Ai\Responses\EmbeddingsResponse;
use Laravel\Ai\Responses\ImageResponse;
use Laravel\Ai\Responses\RerankingResponse;
use Laravel\Ai\Responses\StreamableAgentResponse; // return type only — implemented Phase 5
use Laravel\Ai\Responses\TranscriptionResponse;

/**
 * The stager driver implements every operation exposed by the Laravel AI SDK.
 * It intercepts all AI calls and returns fixture-based responses with zero
 * real API calls and zero token spend.
 *
 * This class implements all provider interfaces so that AiManager's typed
 * provider methods (textProvider, audioProvider, etc.) pass their instanceof
 * checks when returning this driver.
 *
 * Full implementation is added in Phase 5. Gateway methods are intentionally
 * unsupported — the stager never delegates to a real gateway.
 */
class StagerDriver implements AudioProvider, EmbeddingProvider, ImageProvider, RerankingProvider, TextProvider, TranscriptionProvider
{
    // -------------------------------------------------------------------------
    // TextProvider
    // -------------------------------------------------------------------------

    public function prompt(AgentPrompt $prompt): AgentResponse
    {
        // Phase 5: resolve fixture via FixtureResolver, simulate latency, log
        return new AgentResponse(
            invocationId: 'stager-'.uniqid(),
            text: config('ai-stager.agents.*.default', 'Simulated AI response for staging.'),
            usage: new Usage,
            meta: new Meta(provider: 'stager', model: 'stager'),
        );
    }

    public function stream(AgentPrompt $prompt): StreamableAgentResponse
    {
        // Phase 5: return StagerStreamableAgentResponse with word-by-word SSE emission
        throw new \RuntimeException('[AI Stager] stream() not yet implemented — coming in Phase 5.');
    }

    public function textGateway(): TextGateway
    {
        throw new BadMethodCallException('The AI Stager driver does not support gateway access.');
    }

    public function useTextGateway(TextGateway $gateway): static
    {
        throw new BadMethodCallException('The AI Stager driver does not support gateway replacement.');
    }

    public function defaultTextModel(): string
    {
        return 'stager';
    }

    public function cheapestTextModel(): string
    {
        return 'stager';
    }

    public function smartestTextModel(): string
    {
        return 'stager';
    }

    // -------------------------------------------------------------------------
    // AudioProvider
    // -------------------------------------------------------------------------

    public function audio(
        string $text,
        string $voice = 'default-female',
        ?string $instructions = null,
        ?string $model = null,
        int $timeout = 30,
    ): AudioResponse {
        // Phase 5: return StagerAudioResponse wrapping placeholder MP3
        throw new \RuntimeException('[AI Stager] audio() not yet implemented — coming in Phase 5.');
    }

    public function audioGateway(): AudioGateway
    {
        throw new BadMethodCallException('The AI Stager driver does not support gateway access.');
    }

    public function useAudioGateway(AudioGateway $gateway): static
    {
        throw new BadMethodCallException('The AI Stager driver does not support gateway replacement.');
    }

    public function defaultAudioModel(): string
    {
        return 'stager';
    }

    // -------------------------------------------------------------------------
    // ImageProvider
    // -------------------------------------------------------------------------

    public function image(
        string $prompt,
        array $attachments = [],
        ?string $size = null,
        ?string $quality = null,
        ?string $model = null,
        ?int $timeout = null,
    ): ImageResponse {
        // Phase 5: return StagerImageResponse wrapping placeholder PNG
        throw new \RuntimeException('[AI Stager] image() not yet implemented — coming in Phase 5.');
    }

    public function imageGateway(): ImageGateway
    {
        throw new BadMethodCallException('The AI Stager driver does not support gateway access.');
    }

    public function useImageGateway(ImageGateway $gateway): static
    {
        throw new BadMethodCallException('The AI Stager driver does not support gateway replacement.');
    }

    public function defaultImageModel(): string
    {
        return 'stager';
    }

    public function defaultImageOptions(?string $size = null, $quality = null): array
    {
        return [];
    }

    // -------------------------------------------------------------------------
    // EmbeddingProvider
    // -------------------------------------------------------------------------

    /**
     * @param  string[]  $inputs
     */
    public function embeddings(array $inputs, ?int $dimensions = null, ?string $model = null, int $timeout = 30): EmbeddingsResponse
    {
        // Phase 5: return StagerEmbeddingResponse with hash or zero vectors
        throw new \RuntimeException('[AI Stager] embeddings() not yet implemented — coming in Phase 5.');
    }

    public function embeddingGateway(): EmbeddingGateway
    {
        throw new BadMethodCallException('The AI Stager driver does not support gateway access.');
    }

    public function useEmbeddingGateway(EmbeddingGateway $gateway): static
    {
        throw new BadMethodCallException('The AI Stager driver does not support gateway replacement.');
    }

    public function defaultEmbeddingsModel(): string
    {
        return 'stager';
    }

    public function defaultEmbeddingsDimensions(): int
    {
        return (int) config('ai-stager.embeddings.dimensions', 1536);
    }

    // -------------------------------------------------------------------------
    // RerankingProvider
    // -------------------------------------------------------------------------

    /**
     * @param  array<int, string>  $documents
     */
    public function rerank(array $documents, string $query, ?int $limit = null, ?string $model = null): RerankingResponse
    {
        // Phase 5: return StagerRerankResponse with descending fake scores
        throw new \RuntimeException('[AI Stager] rerank() not yet implemented — coming in Phase 5.');
    }

    public function rerankingGateway(): RerankingGateway
    {
        throw new BadMethodCallException('The AI Stager driver does not support gateway access.');
    }

    public function useRerankingGateway(RerankingGateway $gateway): static
    {
        throw new BadMethodCallException('The AI Stager driver does not support gateway replacement.');
    }

    public function defaultRerankingModel(): string
    {
        return 'stager';
    }

    // -------------------------------------------------------------------------
    // TranscriptionProvider
    // -------------------------------------------------------------------------

    public function transcribe(
        TranscribableAudio $audio,
        ?string $language = null,
        bool $diarize = false,
        ?string $model = null,
    ): TranscriptionResponse {
        // Phase 5: return StagerTranscriptionResponse with configured default text
        throw new \RuntimeException('[AI Stager] transcribe() not yet implemented — coming in Phase 5.');
    }

    public function transcriptionGateway(): TranscriptionGateway
    {
        throw new BadMethodCallException('The AI Stager driver does not support gateway access.');
    }

    public function useTranscriptionGateway(TranscriptionGateway $gateway): static
    {
        throw new BadMethodCallException('The AI Stager driver does not support gateway replacement.');
    }

    public function defaultTranscriptionModel(): string
    {
        return 'stager';
    }
}
