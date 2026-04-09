<div class="space-y-3">
    <div class="flex items-center justify-between">
        <p class="text-sm font-medium text-indigo-800">
            @if($isFileBased)
                Editing: <code class="bg-indigo-100 px-1 rounded text-xs">resources/ai-fixtures/{{ $fixturePath }}</code>
            @elseif(config('ai-stager.agents.'.$agentClass))
                Inline fixture — saving will write to <code class="bg-indigo-100 px-1 rounded text-xs">resources/ai-fixtures/{{ $fixturePath }}</code>
            @else
                New fixture — will be created at <code class="bg-indigo-100 px-1 rounded text-xs">resources/ai-fixtures/{{ $fixturePath }}</code>
            @endif
        </p>
        @if($saved)
            <span class="text-xs text-green-600 font-medium">✓ Saved</span>
        @endif
    </div>

    <textarea
        wire:model="content"
        rows="6"
        placeholder="Enter the fixture text to return for this agent in staging…"
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-400 resize-y bg-white"
    ></textarea>

    <div class="flex items-center gap-3">
        <button
            wire:click="save"
            class="text-sm font-medium px-4 py-1.5 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-400"
        >
            {{ $isFileBased ? 'Save' : 'Create fixture' }}
        </button>
    </div>

    @if($configSnippet)
        <div class="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-lg">
            <p class="text-xs font-medium text-amber-800 mb-2">
                Add this to the <code>agents</code> array in <code>config/ai-stager.php</code>:
            </p>
            <pre class="text-xs font-mono text-amber-900 bg-amber-100 p-2 rounded overflow-x-auto">{{ $configSnippet }}</pre>
        </div>
    @endif
</div>
