@extends('pdf.base')
@section('contenido')
<h1>Historial de asistencias</h1>
<div class="sub">Período: {{ $desde ? \Carbon\Carbon::parse($desde)->format('d/m/Y') : 'inicio' }} – {{ $hasta ? \Carbon\Carbon::parse($hasta)->format('d/m/Y') : 'hoy' }}</div>
<table><tr><th>Fecha</th><th>Socio</th><th>DNI</th><th>Ingreso</th><th>Egreso</th><th>Permanencia</th></tr>
@foreach ($asistencias as $a)<tr><td>{{ $a->fecha->format('d/m/Y') }}</td><td>{{ $a->socio->nombre_completo }}</td><td>{{ $a->socio->dni }}</td><td>{{ substr($a->hora_ingreso, 0, 5) }}</td><td>{{ $a->hora_salida ? substr($a->hora_salida, 0, 5) : '—' }}</td><td>{{ $a->permanenciaTexto() }}</td></tr>@endforeach
<tr><th colspan="5" class="r">TOTAL ({{ $asistencias->count() }} asistencias)</th><th>{{ intdiv($minutos, 60) }} h {{ str_pad((string) ($minutos % 60), 2, '0', STR_PAD_LEFT) }} min</th></tr></table>
@endsection
