<div class="flex h-[28rem] flex-col rounded-xl bg-white shadow-sm"
     x-data x-init="$nextTick(() => $refs.lista.scrollTop = $refs.lista.scrollHeight)"
     x-on:mensaje-enviado.window="$nextTick(() => $refs.lista.scrollTop = $refs.lista.scrollHeight)">
    <div x-ref="lista" class="flex-1 space-y-3 overflow-y-auto p-4" aria-live="polite">
        @foreach ($conversacion as $m)
            <div class="flex {{ $m['de'] === 'socio' ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[85%] whitespace-pre-line rounded-2xl px-4 py-2 text-sm {{ $m['de'] === 'socio' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-800' }}">{{ $m['texto'] }}</div>
            </div>
            @if (! empty($m['sugerencias']) && $loop->last)
                <div class="flex flex-wrap gap-2">
                    @foreach ($m['sugerencias'] as $s)
                        <button type="button" wire:click="preguntar(@js($s))" class="rounded-full border px-3 py-1 text-xs text-slate-600 hover:bg-slate-50">{{ $s }}</button>
                    @endforeach
                </div>
            @endif
        @endforeach
    </div>
    <form wire:submit="enviar" x-on:submit="$dispatch('mensaje-enviado')" class="flex gap-2 border-t border-slate-200 p-3">
        <input wire:model="mensaje" type="text" maxlength="300" placeholder="Escribí tu pregunta…" aria-label="Tu pregunta" autocomplete="off"
               class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-300">
        <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700" wire:loading.attr="disabled">Enviar</button>
    </form>
</div>
