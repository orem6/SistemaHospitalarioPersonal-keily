<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// modulo ASII-19 - ingreso de resultados de laboratorio (orem6)
// cambios aditivos sobre el scaffold existente:
//  - lifecycle: un resultado capturado queda "pendiente de publicacion"
//    hasta que el tecnico lo publica (published_at / published_by)
//  - trazabilidad: historial de correcciones controladas de resultados
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lab_results', function (Blueprint $table) {
            $table->dateTime('published_at')->nullable()->after('sent_to_emr_at');
            $table->unsignedBigInteger('published_by')->nullable()->after('published_at');

            $table->foreign('published_by')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });

        // cada modificacion de un resultado no publicado queda registrada
        Schema::create('lab_result_corrections', function (Blueprint $table) {
            $table->id();
            $table->char('tenant_id', 36)->index();
            $table->foreignId('lab_result_id')
                  ->constrained('lab_results')
                  ->cascadeOnDelete();
            $table->foreignId('corrected_by')
                  ->constrained('users')
                  ->restrictOnDelete();
            $table->string('field', 100);
            $table->text('previous_value')->nullable();
            $table->text('new_value')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'lab_result_id', 'created_at'], 'idx_result_correction_trail');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_result_corrections');

        Schema::table('lab_results', function (Blueprint $table) {
            $table->dropForeign(['published_by']);
            $table->dropColumn(['published_by', 'published_at']);
        });
    }
};