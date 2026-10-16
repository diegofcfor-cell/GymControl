@extends('pdf.base')
@section('contenido')
<h1>Comprobante de pago Nº {{ str_pad((string) $pago->id, 6, '0', STR_PAD_LEFT) }}</h1>
<div class="sub">Gimnasio Fénix</div>
<table>
<tr><th width="35%">Socio</th><td>{{ $socio->nombre_completo }}</td></tr>
<tr><th>DNI</th><td>{{ $socio->dni }}</td></tr>
<tr><th>Fecha de pago</th><td>{{ $pago->fecha_pago->format('d/m/Y') }}</td></tr>
<tr><th>Plan</th><td>{{ $pago->membresia->nombre }}</td></tr>
<tr><th>Período cubierto</th><td>{{ $pago->periodo_desde->format('d/m/Y') }} al {{ $pago->periodo_hasta->format('d/m/Y') }}</td></tr>
<tr><th>Método de pago</th><td>{{ ucfirst($pago->metodo_pago) }}</td></tr>
<tr><th>Registrado por</th><td>{{ $pago->operador->name }}</td></tr>
<tr><th>Importe</th><td><strong>$ {{ number_format($pago->importe, 2, ',', '.') }}</strong></td></tr>
</table>
<p class="sub" style="margin-top:10px">Constancia interna de pago. No válida como factura.</p>
@endsection
