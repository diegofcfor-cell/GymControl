<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Pedidos de rectificación o supresión de datos personales (Ley 25.326, art. 16). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes_datos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('socio_id')->constrained('socios');
            $table->string('tipo', 20);                                   // rectificacion | supresion
            $table->text('detalle');
            $table->string('estado', 20)->default('pendiente')->index(); // pendiente | resuelta
            $table->text('respuesta')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users'); // administrador que la resolvió
            $table->timestamp('resuelta_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_datos');
    }
};
