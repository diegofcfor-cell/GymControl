<table class="w-full text-sm">
    <thead><tr class="text-left text-xs uppercase text-slate-500"><th class="py-1">Socio</th><th>Ingreso</th><th>Egreso</th></tr></thead>
    <tbody>
    @forelse ($accesos as $a)
        <tr class="border-t">
            <td class="py-1.5"><a class="hover:underline" href="{{ route('socios.show', $a->socio) }}">{{ $a->socio->nombre_completo }}</a></td>
            <td>{{ substr($a->hora_ingreso, 0, 5) }}</td>
            <td>
                @if ($a->hora_salida)
                    {{ substr($a->hora_salida, 0, 5) }}
                @else
                    <span class="rounded bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700">en sala</span>
                @endif
            </td>
        </tr>
    @empty
        <tr><td colspan="3" class="py-4 text-center text-slate-400">Todavía no hubo accesos hoy.</td></tr>
    @endforelse
    </tbody>
</table>
