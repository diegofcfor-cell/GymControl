<x-layouts.portal titulo="Preguntas frecuentes">
<h1 class="mb-4 text-2xl font-bold">Preguntas frecuentes</h1>

<livewire:chatbot-faq />

@if ($preguntas->isNotEmpty())
    <section class="mt-6 rounded-xl bg-white p-5 shadow-sm">
        <h2 class="mb-2 font-semibold">Todas las preguntas</h2>
        <div class="divide-y">
            @foreach ($preguntas as $p)
                <details class="py-2">
                    <summary class="cursor-pointer text-sm font-medium">{{ $p->pregunta }}</summary>
                    <p class="mt-2 whitespace-pre-line text-sm text-slate-600">{{ $p->respuesta }}</p>
                </details>
            @endforeach
        </div>
    </section>
@endif
</x-layouts.portal>
