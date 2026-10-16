<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preguntas_frecuentes', function (Blueprint $table) {
            $table->id();
            $table->string('pregunta', 200);
            $table->text('respuesta');
            $table->string('palabras_clave')->nullable();   // separadas por coma, ayudan al chatbot a encontrarla
            $table->unsignedSmallInteger('orden')->default(0);
            $table->boolean('activa')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preguntas_frecuentes');
    }
};
