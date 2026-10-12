<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('socios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('membresia_id')->constrained('membresias');
            $table->foreignId('user_id')->constrained('users');   // recepcionista que dio el alta
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->string('dni', 15)->unique();
            $table->string('telefono', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('pin', 6)->unique();
            $table->string('qr_codigo', 36)->unique();
            $table->string('estado', 10)->default('activo')->index(); // activo | inactivo
            $table->date('fecha_alta');
            $table->date('fecha_vencimiento')->nullable()->index();   // fin de la cuota vigente
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('socios');
    }
};
