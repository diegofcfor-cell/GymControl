<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('socio_id')->constrained('socios');
            $table->foreignId('membresia_id')->constrained('membresias');
            $table->foreignId('user_id')->constrained('users');   // quien registró el cobro
            $table->decimal('importe', 10, 2);                    // precio al momento del pago
            $table->date('fecha_pago')->index();
            $table->date('periodo_desde');
            $table->date('periodo_hasta');
            $table->string('metodo_pago', 30)->default('efectivo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
