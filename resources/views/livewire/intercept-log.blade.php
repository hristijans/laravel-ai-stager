<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-lg font-semibold">Intercept Log</h2>
            <p class="text-sm text-gray-500 mt-0.5">{{ $total }} {{ Str::plural('entry', $total) }} recorded</p>
        </div>
        <button
            wire:click="clearLog"
            wire:confirm="Clear all log entries?"
            class="text-sm font-medium px-3 py-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50"
        >
            Clear log
        </button>
    </div>

    @if($cleared)
        <div class="text-center py-12 text-gray-400">Log cleared.</div>
    @elseif(empty($entries))
        <div class="text-center py-12 text-gray-400">
            No intercept log entries yet.<br>
            <span class="text-sm">Enable logging with <code class="bg-gray-100 px-1 rounded">AI_STAGER_LOG=true</code>.</span>
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs tracking-wider">
                    <tr>
                        <th class="text-left px-4 py-3">Operation</th>
                        <th class="text-left px-4 py-3">Agent</th>
                        <th class="text-left px-4 py-3">Context</th>
                        <th class="text-left px-4 py-3">Timestamp</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($entries as $entry)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center text-xs font-medium px-2 py-0.5 rounded-full
                                    {{ match($entry['operation']) {
                                        'prompt', 'stream' => 'bg-indigo-50 text-indigo-700',
                                        'image'            => 'bg-pink-50 text-pink-700',
                                        'audio'            => 'bg-purple-50 text-purple-700',
                                        'embeddings'       => 'bg-teal-50 text-teal-700',
                                        'rerank'           => 'bg-orange-50 text-orange-700',
                                        'transcribe'       => 'bg-sky-50 text-sky-700',
                                        default            => 'bg-gray-100 text-gray-600',
                                    } }}">
                                    {{ $entry['operation'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-600 font-mono text-xs truncate max-w-xs">
                                {{ $entry['agent'] ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs">
                                @if(!empty($entry['context']))
                                    @foreach($entry['context'] as $key => $value)
                                        <span class="mr-2">{{ $key }}: {{ $value }}</span>
                                    @endforeach
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-400 text-xs whitespace-nowrap">
                                {{ $entry['timestamp'] }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($lastPage > 1)
            <div class="flex items-center justify-between mt-4 text-sm text-gray-500">
                <span>Page {{ $currentPage }} of {{ $lastPage }}</span>
                <div class="flex gap-2">
                    @if($currentPage > 1)
                        <button wire:click="previousPage" class="px-3 py-1 rounded border border-gray-200 hover:bg-gray-50">
                            ← Previous
                        </button>
                    @endif
                    @if($currentPage < $lastPage)
                        <button wire:click="nextPage" class="px-3 py-1 rounded border border-gray-200 hover:bg-gray-50">
                            Next →
                        </button>
                    @endif
                </div>
            </div>
        @endif
    @endif
</div>
