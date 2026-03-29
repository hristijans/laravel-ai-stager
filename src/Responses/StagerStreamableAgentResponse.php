<?php

namespace Hristijans\AiStager\Responses;

use Illuminate\Support\Str;
use Laravel\Ai\Responses\Data\Meta;
use Laravel\Ai\Responses\Data\Usage;
use Laravel\Ai\Responses\StreamableAgentResponse;
use Laravel\Ai\Streaming\Events\StreamEnd;
use Laravel\Ai\Streaming\Events\StreamStart;
use Laravel\Ai\Streaming\Events\TextDelta;

/**
 * Factory for creating stager-backed StreamableAgentResponse instances.
 *
 * The returned StreamableAgentResponse emits the fixture text word-by-word
 * using the standard SDK event types (StreamStart → TextDelta... → StreamEnd)
 * so it is fully compatible with usingVercelDataProtocol() and toResponse().
 */
final class StagerStreamableAgentResponse
{
    /**
     * Build a StreamableAgentResponse that emits $text word-by-word.
     */
    public static function make(string $text, string $invocationId = ''): StreamableAgentResponse
    {
        $invocationId = $invocationId ?: 'stager-'.Str::uuid();

        return new StreamableAgentResponse(
            invocationId: $invocationId,
            generator: static function () use ($text, $invocationId) {
                $messageId = (string) Str::uuid();
                $now = (int) (microtime(true) * 1000);

                yield (new StreamStart(
                    id: (string) Str::uuid(),
                    provider: 'stager',
                    model: 'stager',
                    timestamp: $now,
                ))->withInvocationId($invocationId);

                $words = preg_split('/(\s+)/', $text, flags: PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

                foreach ($words as $chunk) {
                    yield (new TextDelta(
                        id: (string) Str::uuid(),
                        messageId: $messageId,
                        delta: $chunk,
                        timestamp: (int) (microtime(true) * 1000),
                    ))->withInvocationId($invocationId);
                }

                yield (new StreamEnd(
                    id: (string) Str::uuid(),
                    reason: 'end_turn',
                    usage: new Usage,
                    timestamp: (int) (microtime(true) * 1000),
                ))->withInvocationId($invocationId);
            },
            meta: new Meta(provider: 'stager', model: 'stager'),
        );
    }
}
