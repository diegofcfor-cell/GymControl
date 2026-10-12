@extends('pdf.base')
@section('contenido')
<table style="border:none"><tr><td style="border:none"><h1>Ficha personal del socio</h1><div class="sub">Gimnasio Fénix</div></td>
<td style="border:none" class="r"><img src="data:image/svg+xml;base64,{{ $qr }}" width="90" height="90"></td></tr></table>
<h2>Datos personales</h2>
<table>
<tr><th width="20%">Apellido y nombre</th><td>{{ $socio->nombre_completo }}</td><th width="15%">DNI</th><td>{{ $socio->dni }}</td></tr>
<tr><th>Teléfono</th><td>{{ $socio->telefono ?: '—' }}</td><th>Correo</th><td>{{ $socio->email ?: '—' }}</td></tr>
<tr><th>Membresía</th><td>{{ $socio->membresia->nombre }} ({{ $socio->membresia->horas_diarias_max ? $socio->membresia->horas_diarias_max.' h diarias' : 'pase libre' }})</td><th>PIN</th><td>{{ $socio->pin }}</td></tr>
<tr><th>Fecha de alta</th><td>{{ $socio->fecha_alta->format('d/m/Y') }} (por {{ $socio->operador->name }})</td><th>Cuota vence</th><td>{{ $socio->fecha_vencimiento?->format('d/m/Y') ?? '—' }}</td></tr>
</table>
<h2>Aptos físicos</h2>
<table><tr><th>Emisión</th><th>Vencimiento</th><th>Estado</th><th>Archivo digital</th></tr>
@forelse ($aptos as $a)<tr><td>{{ $a->fecha_emision->format('d/m/Y') }}</td><td>{{ $a->fecha_vencimiento->format('d/m/Y') }}</td><td>{{ $a->estaVigente() ? 'vigente' : 'vencido' }}</td><td>{{ $a->archivo ? 'sí' : 'no' }}</td></tr>
@empty<tr><td colspan="4">Sin registros.</td></tr>@endforelse</table>
<h2>Historial de pagos</h2>
<table><tr><th>Fecha</th><th>Plan</th><th>Período</th><th>Método</th><th class="r">Importe</th></tr>
@forelse ($pagos as $p)<tr><td>{{ $p->fecha_pago->format('d/m/Y') }}</td><td>{{ $p->membresia->nombre }}</td><td>{{ $p->periodo_desde->format('d/m/Y') }} al {{ $p->periodo_hasta->format('d/m/Y') }}</td><td>{{ $p->metodo_pago }}</td><td class="r">$ {{ number_format($p->importe, 2, ',', '.') }}</td></tr>
@empty<tr><td colspan="5">Sin pagos.</td></tr>@endforelse</table>
<h2>Últimas asistencias</h2>
<table><tr><th>Fecha</th><th>Ingreso</th><th>Egreso</th><th>Permanencia</th></tr>
@forelse ($asistencias as $a)<tr><td>{{ $a->fecha->format('d/m/Y') }}</td><td>{{ substr($a->hora_ingreso, 0, 5) }}</td><td>{{ $a->hora_salida ? substr($a->hora_salida, 0, 5) : '—' }}</td><td>{{ $a->permanenciaTexto() }}</td></tr>
@empty<tr><td colspan="4">Sin asistencias.</td></tr>@endforelse</table>
@endsection
