<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aptos_fisicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('socio_id')->constrained('socios');
            $table->foreignId('user_id')->constrained('users');   // quien cargó el certificado
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento')->index();
            $table->string('estado', 10)->default('vigente');     // vigente | vencido
            $table->string('archivo')->nullable();                // ruta del PDF o imagen
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aptos_fisicos');
    }
};
