<?php

declare(strict_types=1);

namespace Hristijans\AiStager\Fixtures;

/**
 * Generates deterministic float vectors from an input string via SHA-256.
 *
 * Each call with the same input and dimensions returns an identical vector,
 * making similarity comparisons meaningful within a staging session.
 *
 * Strategy: expand the input into enough bytes by hashing
 * SHA-256(input || 0x00 || counter) for increasing counters, then
 * unpack each 4-byte chunk as an unsigned int and normalize to [-1, 1].
 */
class HashVector
{
    /**
     * Generate a deterministic float vector for the given input.
     *
     * @return float[]
     */
    public static function generate(string $input, int $dimensions): array
    {
        $needed = $dimensions * 4; // 4 bytes per float
        $bytes = '';
        $counter = 0;

        while (strlen($bytes) < $needed) {
            $bytes .= hash('sha256', $input."\x00".pack('N', $counter++), binary: true);
        }

        $vector = [];

        for ($i = 0; $i < $dimensions; $i++) {
            /** @var array<int, int> $unpacked */
            $unpacked = unpack('N', substr($bytes, $i * 4, 4));
            $int = $unpacked[1];
            // Map [0, 0xFFFFFFFF] to [-1.0, 1.0]
            $vector[] = ($int / 0xFFFFFFFF) * 2.0 - 1.0;
        }

        return $vector;
    }
}
