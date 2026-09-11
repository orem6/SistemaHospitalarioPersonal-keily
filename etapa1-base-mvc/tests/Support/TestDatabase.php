<?php

declare(strict_types=1);

namespace LabResults\Tests\Support;

use LabResults\AppContainer;
use LabResults\Persistence\Database\SchemaInstaller;

/**
 * Fábrica de bases SQLite temporales para pruebas deterministas.
 */
final class TestDatabase
{
    public static function crear(): AppContainer
    {
        $ruta = sys_get_temp_dir() . '/lab_results_test_' . uniqid('', true) . '.sqlite';

        $pdo = SchemaInstaller::install($ruta);

        $insertarPrueba = $pdo->prepare(
            'INSERT INTO lab_test_definitions (code, name, result_type, unit)
             VALUES (:code, :name, :result_type, :unit)'
        );

        // Catálogo ficticio de pruebas.
        $insertarPrueba->execute([':code' => 'HEMO', ':name' => 'Hemoglobina',      ':result_type' => 'NUMERICO', ':unit' => 'g/dL']);
        $insertarPrueba->execute([':code' => 'GLU',  ':name' => 'Glucosa en ayuno', ':result_type' => 'NUMERICO', ':unit' => 'mg/dL']);
        $insertarPrueba->execute([':code' => 'UROC', ':name' => 'Urocultivo',       ':result_type' => 'TEXTO',    ':unit' => null]);

        $idHemo = (int) $pdo->query("SELECT id FROM lab_test_definitions WHERE code = 'HEMO'")->fetchColumn();
        $idGlu = (int) $pdo->query("SELECT id FROM lab_test_definitions WHERE code = 'GLU'")->fetchColumn();

        $insertarMuestra = $pdo->prepare(
            'INSERT INTO samples
                 (id, tenant_id, patient_ref, barcode, test_id, status, rejection_reason, collected_at)
             VALUES (:id, :tenant_id, :patient_ref, :barcode, :test_id, :status, :rejection_reason, :collected_at)'
        );

        $tenant = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa1';

        // Muestras ficticias para los casos obligatorios.
        $insertarMuestra->execute([
            ':id' => '11111111-1111-4111-8111-111111111111',
            ':tenant_id' => $tenant,
            ':patient_ref' => 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbb01',
            ':barcode' => 'TEST-BAR-0001',
            ':test_id' => $idHemo,
            ':status' => 'ACEPTADA',
            ':rejection_reason' => null,
            ':collected_at' => '2026-08-18 08:15:00',
        ]);
        $insertarMuestra->execute([
            ':id' => '22222222-2222-4222-8222-222222222222',
            ':tenant_id' => $tenant,
            ':patient_ref' => 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbb02',
            ':barcode' => 'TEST-BAR-0002',
            ':test_id' => $idGlu,
            ':status' => 'RECHAZADA',
            ':rejection_reason' => 'Muestra hemolizada (ficticio)',
            ':collected_at' => '2026-08-18 09:40:00',
        ]);

        return AppContainer::conSqlite($ruta);
    }
}
