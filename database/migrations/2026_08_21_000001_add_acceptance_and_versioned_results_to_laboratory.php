<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ASII-19 · SEGUNDA ETAPA — ingreso de resultados de laboratorio (orem6)
// Cambios ADITIVOS y reversibles sobre el scaffold existente:
//
//  1) samples: columnas nuevas nullable acceptance_status / rejection_reason
//     para representar la decisión de aceptación de la muestra sin alterar
//     el enum `status` original (recepción de muestras NO es parte de este
//     módulo; aquí solo se CONSUME ese estado).
//
//  2) lab_result_versions: resultados VERSIONADOS e inmutables.
//     Una corrección SIEMPRE inserta una fila nueva con version_number + 1;
//     la versión anterior jamás se sobrescribe ni elimina.
//
// Compatible PostgreSQL / SQLite a través del schema builder.

return new class extends Migration
{
    public function up(): void
    {
        // catálogo: tipo esperado y código corto para validar tipo/unidad
        Schema::table('lab_tests', function (Blueprint $table) {
            $table->string('code', 20)->nullable()->after('name');
            $table->string('result_type', 10)->default('NUMERICO')->after('unit');
        });

        Schema::table('samples', function (Blueprint $table) {
            $table->string('acceptance_status', 20)->nullable()->after('status');
            $table->text('rejection_reason')->nullable()->after('acceptance_status');

            // prueba específica que materializa la muestra (vertical del módulo;
            // los pedidos multi-prueba siguen atendidos por el flujo semana 2)
            $table->foreignId('lab_test_id')
                  ->nullable()
                  ->after('rejection_reason')
                  ->constrained('lab_tests')
                  ->nullOnDelete();

            // cola de captura del módulo por hospital
            $table->index(
                ['tenant_id', 'acceptance_status'],
                'idx_samples_acceptance'
            );
        });

        Schema::create('lab_result_versions', function (Blueprint $table) {
            $table->id();
            $table->char('tenant_id', 36)->index();

            $table->foreignId('sample_id')
                  ->constrained('samples')
                  ->restrictOnDelete();

            $table->foreignId('lab_test_id')
                  ->constrained('lab_tests')
                  ->restrictOnDelete();

            $table->unsignedBigInteger('entered_by')
                  ->nullable()
                  ->comment('referencia lógica al usuario del hospital');

            $table->unsignedInteger('version_number');

            $table->string('result_type', 10);           // NUMERICO | TEXTO
            $table->decimal('numeric_value', 12, 4)->nullable();
            $table->string('text_value', 500)->nullable();
            $table->string('unit', 50)->nullable();

            $table->boolean('is_abnormal')->default(false);
            $table->boolean('is_critical')->default(false);

            $table->unsignedBigInteger('corrected_from_version')->nullable();
            $table->text('correction_reason')->nullable();

            $table->dateTime('resulted_at');
            $table->timestamps();

            // una sola fila por versión de cada muestra
            $table->unique(
                ['sample_id', 'version_number'],
                'uq_result_version_per_sample'
            );

            // historial completo por muestra (regla 5: siempre disponible)
            $table->index(['sample_id', 'version_number'], 'idx_versions_sample_history');

            // trazabilidad de correcciones por hospital
            $table->index(['tenant_id', 'corrected_from_version'], 'idx_versions_correction_origin');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_result_versions');

        Schema::table('samples', function (Blueprint $table) {
            $table->dropIndex('idx_samples_acceptance');
            $table->dropConstrainedForeignId('lab_test_id');
            $table->dropColumn(['rejection_reason', 'acceptance_status']);
        });

        Schema::table('lab_tests', function (Blueprint $table) {
            $table->dropColumn(['result_type', 'code']);
        });
    }
};
