<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('socio_id')->constrained('socios');
            $table->date('fecha')->index();
            $table->time('hora_ingreso');
            $table->time('hora_salida')->nullable();            // NULL hasta que egresa
            $table->time('tiempo_permanencia')->nullable();     // NULL hasta que egresa
            $table->timestamps();
            $table->index(['socio_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asistencias');
    }
};
