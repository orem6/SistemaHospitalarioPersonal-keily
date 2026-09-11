<?php

declare(strict_types=1);

namespace LabResults\Persistence\Database;

/**
 * Datos semilla 100% FICTICIOS para demostración y pruebas manuales.
 * INSERT OR IGNORE: idempotente, seguro de ejecutar varias veces.
 */
final class DemoSeeder
{
    /** UUID lógico del hospital local (escritura clínica). */
    public const TENANT_DEMO = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa1';

    /** UUID lógico del paciente en el sistema CENTRAL (solo referencia, sin FK remota). */
    public const PATIENT_REF_1 = 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbb01';
    public const PATIENT_REF_2 = 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbb02';
    public const PATIENT_REF_3 = 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbb03';

    /** CASO 1: muestra ACEPTADA -> permite ingreso del resultado. */
    public const MUESTRA_ACEPTADA_ID = '11111111-1111-4111-8111-111111111111';

    /** Segunda muestra ACEPTADA (para demostrar rechazo de valores inválidos). */
    public const MUESTRA_ACEPTADA_2_ID = '44444444-4444-4444-8444-444444444444';

    /** CASO 2: muestra RECHAZADA -> NO permite ingreso del resultado. */
    public const MUESTRA_RECHAZADA_ID = '22222222-2222-4222-8222-222222222222';

    /** Muestra PENDIENTE -> aún no aceptada; tampoco admite resultados. */
    public const MUESTRA_PENDIENTE_ID = '33333333-3333-4333-8333-333333333333';

    public static function seed(\PDO $pdo): void
    {
        $insertTest = $pdo->prepare(
            'INSERT OR IGNORE INTO lab_test_definitions (code, name, result_type, unit)
             VALUES (:code, :name, :result_type, :unit)'
        );
        $insertSample = $pdo->prepare(
            'INSERT OR IGNORE INTO samples
                 (id, tenant_id, patient_ref, barcode, test_id, status, rejection_reason, collected_at)
             VALUES
                 (:id, :tenant_id, :patient_ref, :barcode,
                  (SELECT id FROM lab_test_definitions WHERE code = :code),
                  :status, :rejection_reason, :collected_at)'
        );

        $tests = [
            ['code' => 'HEMO', 'name' => 'Hemoglobina',      'result_type' => 'NUMERICO', 'unit' => 'g/dL'],
            ['code' => 'GLU',  'name' => 'Glucosa en ayuno', 'result_type' => 'NUMERICO', 'unit' => 'mg/dL'],
            ['code' => 'UROC', 'name' => 'Urocultivo',       'result_type' => 'TEXTO',    'unit' => null],
        ];
        foreach ($tests as $test) {
            $insertTest->execute($test);
        }

        $samples = [
            [
                ':id' => self::MUESTRA_ACEPTADA_ID,
                ':tenant_id' => self::TENANT_DEMO,
                ':patient_ref' => self::PATIENT_REF_1,
                ':barcode' => 'DEM-BAR-0001',
                ':code' => 'HEMO',
                ':status' => 'ACEPTADA',
                ':rejection_reason' => null,
                ':collected_at' => '2026-08-18 08:15:00',
            ],
            [
                ':id' => self::MUESTRA_RECHAZADA_ID,
                ':tenant_id' => self::TENANT_DEMO,
                ':patient_ref' => self::PATIENT_REF_2,
                ':barcode' => 'DEM-BAR-0002',
                ':code' => 'GLU',
                ':status' => 'RECHAZADA',
                ':rejection_reason' => 'Muestra hemolizada (dato ficticio de demostración)',
                ':collected_at' => '2026-08-18 09:40:00',
            ],
            [
                ':id' => self::MUESTRA_ACEPTADA_2_ID,
                ':tenant_id' => self::TENANT_DEMO,
                ':patient_ref' => self::PATIENT_REF_1,
                ':barcode' => 'DEM-BAR-0004',
                ':code' => 'HEMO',
                ':status' => 'ACEPTADA',
                ':rejection_reason' => null,
                ':collected_at' => '2026-08-18 11:30:00',
            ],
            [
                ':id' => self::MUESTRA_PENDIENTE_ID,
                ':tenant_id' => self::TENANT_DEMO,
                ':patient_ref' => self::PATIENT_REF_3,
                ':barcode' => 'DEM-BAR-0003',
                ':code' => 'UROC',
                ':status' => 'PENDIENTE',
                ':rejection_reason' => null,
                ':collected_at' => '2026-08-18 10:05:00',
            ],
        ];

        foreach ($samples as $sample) {
            $insertSample->execute($sample);
        }
    }
}
