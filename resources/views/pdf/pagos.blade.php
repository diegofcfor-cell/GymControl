@extends('pdf.base')
@section('contenido')
<h1>Historial de pagos</h1>
<div class="sub">Período: {{ $desde ? \Carbon\Carbon::parse($desde)->format('d/m/Y') : 'inicio' }} – {{ $hasta ? \Carbon\Carbon::parse($hasta)->format('d/m/Y') : 'hoy' }}</div>
<table><tr><th>Fecha</th><th>Socio</th><th>DNI</th><th>Plan</th><th>Período cubierto</th><th>Método</th><th>Cobró</th><th class="r">Importe</th></tr>
@foreach ($pagos as $p)<tr><td>{{ $p->fecha_pago->format('d/m/Y') }}</td><td>{{ $p->socio->nombre_completo }}</td><td>{{ $p->socio->dni }}</td><td>{{ $p->membresia->nombre }}</td><td>{{ $p->periodo_desde->format('d/m/Y') }} al {{ $p->periodo_hasta->format('d/m/Y') }}</td><td>{{ $p->metodo_pago }}</td><td>{{ $p->operador->name }}</td><td class="r">$ {{ number_format($p->importe, 2, ',', '.') }}</td></tr>@endforeach
<tr><th colspan="7" class="r">TOTAL ({{ $pagos->count() }} pagos)</th><th class="r">$ {{ number_format($total, 2, ',', '.') }}</th></tr></table>
@endsection
