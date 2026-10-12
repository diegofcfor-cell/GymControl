<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membresias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50);                       // Básica | Media | Premium
            $table->decimal('precio', 10, 2);
            $table->unsignedSmallInteger('duracion_dias')->default(30);
            $table->unsignedSmallInteger('horas_diarias_max')->nullable(); // NULL = pase libre
            $table->string('estado', 10)->default('activa');     // activa | inactiva
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membresias');
    }
};
