<?php

declare(strict_types=1);

namespace Hristijans\AiStager;

use BadMethodCallException;
use Hristijans\AiStager\Fixtures\FixtureResolver;
use Hristijans\AiStager\Fixtures\HashVector;
use Hristijans\AiStager\Responses\StagerAgentResponse;
use Hristijans\AiStager\Support\InterceptLogger;
use Hristijans\AiStager\Responses\StagerAudioResponse;
use Hristijans\AiStager\Responses\StagerEmbeddingsResponse;
use Hristijans\AiStager\Responses\StagerImageResponse;
use Hristijans\AiStager\Responses\StagerRerankingResponse;
use Hristijans\AiStager\Responses\StagerStreamableAgentResponse;
use Hristijans\AiStager\Responses\StagerTranscriptionResponse;
use Illuminate\Support\Facades\Log;
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
use Laravel\Ai\Responses\AudioResponse;
use Laravel\Ai\Responses\EmbeddingsResponse;
use Laravel\Ai\Responses\ImageResponse;
use Laravel\Ai\Responses\RerankingResponse;
use Laravel\Ai\Responses\StreamableAgentResponse;
use Laravel\Ai\Responses\TranscriptionResponse;

/**
 * The stager driver implements every operation exposed by the Laravel AI SDK.
 * It intercepts all AI calls and returns fixture-based responses with zero
 * real API calls and zero token spend.
 *
 * This class implements all provider interfaces so that AiManager's typed
 * provider methods (textProvider, audioProvider, etc.) pass their instanceof
 * checks when returning this driver.
 */
class StagerDriver implements AudioProvider, EmbeddingProvider, ImageProvider, RerankingProvider, TextProvider, TranscriptionProvider
{
    public function __construct(
        private readonly FixtureResolver $fixtures,
        private readonly InterceptLogger $logger,
    ) {}

    // -------------------------------------------------------------------------
    // TextProvider
    // -------------------------------------------------------------------------

    public function prompt(AgentPrompt $prompt): StagerAgentResponse
    {
        $this->intercept('prompt', ['agent' => get_class($prompt->agent)]);

        return StagerAgentResponse::make($this->fixtures->resolve($prompt));
    }

    public function stream(AgentPrompt $prompt): StreamableAgentResponse
    {
        $this->intercept('stream', ['agent' => get_class($prompt->agent)]);

        return StagerStreamableAgentResponse::make($this->fixtures->resolve($prompt));
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
        $this->intercept('audio');

        return StagerAudioResponse::make();
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
        $this->intercept('image');

        return StagerImageResponse::make();
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
        $this->intercept('embeddings', ['inputs' => count($inputs)]);

        $dimensions = $dimensions ?? $this->defaultEmbeddingsDimensions();
        $strategy = config('ai-stager.embeddings.strategy', 'hash');

        $vectors = $strategy === 'hash'
            ? array_map(fn (string $input) => HashVector::generate($input, $dimensions), $inputs)
            : null;

        return StagerEmbeddingsResponse::make($inputs, $dimensions, $vectors);
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
        $this->intercept('rerank', ['documents' => count($documents)]);

        return StagerRerankingResponse::make($documents, $limit);
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
        $this->intercept('transcribe');

        $text = (string) config('ai-stager.transcription.default', 'This is a simulated transcription for the staging environment.');

        return StagerTranscriptionResponse::make($text);
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

    // -------------------------------------------------------------------------
    // Internal
    // -------------------------------------------------------------------------

    /**
     * Log the interception and simulate configured latency.
     *
     * @param  array<string, mixed>  $context
     */
    private function intercept(string $operation, array $context = []): void
    {
        if (config('ai-stager.log', false)) {
            Log::info('[AI Stager] Intercepted '.$operation, $context);
            $this->logger->record($operation, $context);
        }

        $ms = (int) config('ai-stager.latency_ms', 0);

        if ($ms > 0) {
            usleep($ms * 1000);
        }
    }
}
