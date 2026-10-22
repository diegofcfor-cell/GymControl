<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('socios', function (Blueprint $table) {
            // Fecha en que el socio aceptó el aviso de privacidad (Ley 25.326, art. 5). NULL = sin consentimiento.
            $table->timestamp('consentimiento_datos_at')->nullable()->after('fecha_vencimiento');
        });
    }

    public function down(): void
    {
        Schema::table('socios', function (Blueprint $table) {
            $table->dropColumn('consentimiento_datos_at');
        });
    }
};
