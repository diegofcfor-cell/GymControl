<?php

namespace App\Console\Commands;

use App\Models\AptoFisico;
use Illuminate\Console\Command;

class ActualizarVencimientos extends Command
{
    protected $signature = 'gym:actualizar-vencimientos';

    protected $description = 'Marca como vencidos los aptos físicos cuya fecha de vencimiento ya pasó';

    public function handle(): int
    {
        $n = AptoFisico::where('estado', 'vigente')->whereDate('fecha_vencimiento', '<', today())->update(['estado' => 'vencido']);
        $this->info("Aptos físicos marcados como vencidos: {$n}");

        return self::SUCCESS;
    }
}
