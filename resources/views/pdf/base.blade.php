<!DOCTYPE html>
<html lang="es"><head><meta charset="utf-8">
<style>
 @page { margin: 28px 32px; }
 body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1e293b; }
 h1 { font-size: 16px; margin: 0; } h2 { font-size: 12px; margin: 14px 0 4px; border-bottom: 1px solid #94a3b8; padding-bottom: 2px; }
 .sub { color: #64748b; margin-bottom: 8px; }
 table { width: 100%; border-collapse: collapse; } th { background: #e2e8f0; text-align: left; } th, td { border: 1px solid #cbd5e1; padding: 3px 5px; }
 .r { text-align: right; } .foot { position: fixed; bottom: -14px; font-size: 8px; color: #94a3b8; }
</style></head><body>
<div class="foot">Gym Control · Gimnasio Fénix · Emitido el {{ now()->format('d/m/Y H:i') }}</div>
@yield('contenido')
</body></html>
