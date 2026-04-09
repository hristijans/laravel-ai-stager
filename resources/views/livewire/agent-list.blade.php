<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-lg font-semibold">Agents</h2>
            <p class="text-sm text-gray-500 mt-0.5">Scanned from <code class="bg-gray-100 px-1 rounded">app/</code></p>
        </div>
        <input
            wire:model.live.debounce.300ms="search"
            type="text"
            placeholder="Search agents…"
            class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 w-64"
        >
    </div>

    @if(empty($this->agents))
        <div class="text-center py-16 text-gray-400">
            @if($search)
                No agents matching "<strong>{{ $search }}</strong>".
            @else
                No Agent implementations found in <code>app/</code>.
            @endif
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs tracking-wider">
                    <tr>
                        <th class="text-left px-4 py-3">Agent</th>
                        <th class="text-left px-4 py-3">Strategy</th>
                        <th class="text-left px-4 py-3">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($this->agents as $agent)
                        <tr class="hover:bg-gray-50 {{ $selectedAgent === $agent['class'] ? 'bg-indigo-50' : '' }}">
                            <td class="px-4 py-3">
                                <span class="font-medium text-gray-800">{{ $agent['shortName'] }}</span>
                                <span class="block text-xs text-gray-400 font-mono truncate max-w-xs">{{ $agent['class'] }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $agent['strategy'] }}</td>
                            <td class="px-4 py-3">
                                @if($agent['isExplicit'])
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-green-700 bg-green-50 px-2 py-0.5 rounded-full">
                                        ✓ explicit
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full">
                                        ~ catch-all
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button
                                    wire:click="selectAgent('{{ $agent['class'] }}')"
                                    class="text-xs font-medium px-3 py-1 rounded-lg border
                                        {{ $selectedAgent === $agent['class']
                                            ? 'border-indigo-300 text-indigo-700 bg-indigo-50'
                                            : 'border-gray-200 text-gray-600 hover:border-indigo-300 hover:text-indigo-700' }}"
                                >
                                    {{ $selectedAgent === $agent['class'] ? 'Close' : ($agent['isExplicit'] ? 'Edit' : 'Create') }}
                                </button>
                            </td>
                        </tr>
                        @if($selectedAgent === $agent['class'])
                            <tr>
                                <td colspan="4" class="px-4 py-4 bg-indigo-50 border-t border-indigo-100">
                                    @livewire('ai-stager-fixture-editor', ['agentClass' => $agent['class']], key($agent['class']))
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
