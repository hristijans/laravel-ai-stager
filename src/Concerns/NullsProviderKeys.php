<?php

namespace Hristijans\AiStager\Concerns;

use Illuminate\Support\Facades\Log;

/**
 * Safety net: nulls all real API keys at boot time.
 *
 * Even if the StagerAiManager decorator fails to intercept a call (e.g. due to
 * an SDK update that changes internal routing), the upstream provider will reject
 * the request with an authentication error rather than silently spending tokens.
 *
 * Philosophy: prefer a broken staging app over a surprise API bill.
 */
trait NullsProviderKeys
{
    protected function nullProviderKeys(): void
    {
        $providers = config('ai.providers', []);
        $nulled = [];

        foreach ($providers as $name => $providerConfig) {
            foreach (['key', 'secret', 'token', 'api_key'] as $keyField) {
                if (isset($providerConfig[$keyField])) {
                    config(["ai.providers.{$name}.{$keyField}" => 'ai-stager-disabled']);
                    $nulled[] = $name;
                }
            }
        }

        $nulled = array_unique($nulled);

        Log::info('[AI Stager] All provider API keys have been nulled. Real API calls will fail with auth errors if they escape the stager.', [
            'nulled_providers' => $nulled,
        ]);
    }
}
