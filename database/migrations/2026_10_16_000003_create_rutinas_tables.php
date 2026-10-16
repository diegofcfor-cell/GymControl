<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rutinas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('socio_id')->constrained('socios');
            $table->foreignId('user_id')->constrained('users');      // profesor que la armó
            $table->string('nombre', 100);
            $table->string('objetivo', 100)->nullable();
            $table->text('observaciones')->nullable();
            $table->boolean('activa')->default(true);                // solo una activa por socio
            $table->timestamps();
            $table->index(['socio_id', 'activa']);
        });

        Schema::create('rutina_ejercicios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rutina_id')->constrained('rutinas')->cascadeOnDelete();
            $table->string('dia', 30);                               // "Día 1", "Lunes", "Tren superior"…
            $table->string('ejercicio', 100);
            $table->unsignedTinyInteger('series')->nullable();
            $table->string('repeticiones', 20)->nullable();          // "10-12", "30 s"…
            $table->string('descanso', 20)->nullable();
            $table->string('notas', 200)->nullable();
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();
        });

        Schema::create('solicitudes_rutina', function (Blueprint $table) {
            $table->id();
            $table->foreignId('socio_id')->constrained('socios');
            $table->string('objetivo', 50);
            $table->unsignedTinyInteger('dias_por_semana');
            $table->string('nivel', 20);                             // principiante | intermedio | avanzado
            $table->text('comentarios')->nullable();                 // lesiones, preferencias…
            $table->string('estado', 15)->default('pendiente')->index(); // pendiente | atendida
            $table->foreignId('rutina_id')->nullable()->constrained('rutinas')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_rutina');
        Schema::dropIfExists('rutina_ejercicios');
        Schema::dropIfExists('rutinas');
    }
};
