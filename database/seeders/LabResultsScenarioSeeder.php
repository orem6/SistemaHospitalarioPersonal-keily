<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\LabOrder;
use App\Models\LabOrderItem;
use App\Models\LabTest;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Sample;
use App\Models\SoapNote;
use App\Models\Specialty;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * Escenario ficticio para el módulo ASII-19 (orem6).
 *
 * Crea dos tenants aislados, cada uno con un examen de laboratorio pendiente
 * de captura. Permite demostrar el flujo de captura/corrección/publicación y
 * el aislamiento por tenant sin exponer datos reales.
 */
class LabResultsScenarioSeeder extends Seeder
{
    use WithoutModelEvents;

    public const TENANT_LAB_ID = '00000000-0000-4000-8000-0000000000a1';

    public const TENANT_LAB_SLUG = 'lab-resultados-demo';

    public const TENANT_OTHER_ID = '00000000-0000-4000-8000-0000000000a2';

    public const TENANT_OTHER_SLUG = 'otro-hospital-demo';

    public function run(): void
    {
        $this->seedRoles();

        $labTenant = $this->tenant(self::TENANT_LAB_ID, self::TENANT_LAB_SLUG, 'Laboratorio Central Demo');
        $otherTenant = $this->tenant(self::TENANT_OTHER_ID, self::TENANT_OTHER_SLUG, 'Hospital Vecino Demo');

        $this->seedMainScenario($labTenant);
        $this->seedIsolationScenario($otherTenant);
    }

    private function seedRoles(): void
    {
        foreach (['Admin', 'Médico', 'TecnicoLab', 'Recepcionista'] as $role) {
            Role::query()->firstOrCreate(['name' => $role, 'guard_name' => 'api']);
        }
    }

    private function tenant(string $id, string $slug, string $name): Tenant
    {
        return Tenant::query()->firstOrCreate(
            ['slug' => $slug],
            ['id' => $id, 'name' => $name, 'data' => []]
        );
    }

    private function seedMainScenario(Tenant $tenant): void
    {
        $labTech = $this->user($tenant, 'lab.resultados@demo.local', 'Técnico Resultados Demo', 'TecnicoLab');
        $medico = $this->user($tenant, 'medico.resultados@demo.local', 'Dra. Demo Resultados', 'Médico');
        $this->user($tenant, 'recepcionista@demo.local', 'Recepción Resultados Demo', 'Recepcionista');

        $specialty = $this->specialty($tenant, 'Medicina Interna');
        $patient = $this->patient($tenant, 'PAC-DEMO-0001', 'Ana', 'Pérez');
        $doctor = $this->doctor($medico, $tenant, $specialty, 'MED-DEMO-0001');
        $record = $this->medicalRecord($patient, $tenant, 'EXP-DEMO-0001');
        $soapNote = $this->soapNote($record, $doctor, $tenant);
        $test = $this->labTest($tenant, 'Glucosa', 70.0, 110.0, 40.0, 400.0);

        $order = $this->pendingOrder(
            $tenant,
            $patient,
            $soapNote,
            $record,
            $medico,
            'LAB-DEMO-0001',
            'Control de glucosa'
        );

        $sample = $this->sample($tenant, $order, $labTech, 'BC-DEMO-000001');
        $this->pendingItem($order, $test);
    }

    private function seedIsolationScenario(Tenant $tenant): void
    {
        $labTech = $this->user($tenant, 'lab.otro@demo.local', 'Técnico Otro Hospital', 'TecnicoLab');
        $medico = $this->user($tenant, 'medico.otro@demo.local', 'Dr. Otro Hospital', 'Médico');

        $specialty = $this->specialty($tenant, 'Medicina Interna');
        $patient = $this->patient($tenant, 'PAC-DEMO-0002', 'Carlos', 'López');
        $doctor = $this->doctor($medico, $tenant, $specialty, 'MED-DEMO-0002');
        $record = $this->medicalRecord($patient, $tenant, 'EXP-DEMO-0002');
        $soapNote = $this->soapNote($record, $doctor, $tenant);
        $test = $this->labTest($tenant, 'Hemoglobina', 12.0, 16.0, 7.0, 22.0);

        $order = $this->pendingOrder(
            $tenant,
            $patient,
            $soapNote,
            $record,
            $medico,
            'LAB-DEMO-0002',
            'Control de hemoglobina'
        );

        $sample = $this->sample($tenant, $order, $labTech, 'BC-DEMO-000002');
        $this->pendingItem($order, $test);
    }

    private function user(Tenant $tenant, string $email, string $name, string $role): User
    {
        $user = User::query()->firstOrCreate(
            ['email' => $email],
            [
                'tenant_id' => $tenant->id,
                'name' => $name,
                'password' => Hash::make('password'),
            ]
        );

        if (! $user->hasRole($role)) {
            $user->assignRole($role);
        }

        return $user;
    }

    private function specialty(Tenant $tenant, string $name): Specialty
    {
        return Specialty::query()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => $name],
            ['tenant_id' => $tenant->id, 'name' => $name, 'description' => null]
        );
    }

    private function patient(Tenant $tenant, string $code, string $firstName, string $lastName): Patient
    {
        return Patient::query()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => $code],
            [
                'tenant_id' => $tenant->id,
                'code' => $code,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'birth_date' => '1990-01-01',
                'gender' => 'F',
            ]
        );
    }

    private function doctor(User $user, Tenant $tenant, Specialty $specialty, string $license): Doctor
    {
        return Doctor::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'tenant_id' => $tenant->id,
                'specialty_id' => $specialty->id,
                'license_number' => $license,
                'phone' => null,
            ]
        );
    }

    private function medicalRecord(Patient $patient, Tenant $tenant, string $recordNumber): MedicalRecord
    {
        return MedicalRecord::query()->firstOrCreate(
            ['patient_id' => $patient->id],
            [
                'tenant_id' => $tenant->id,
                'record_number' => $recordNumber,
                'opened_at' => today(),
            ]
        );
    }

    private function soapNote(MedicalRecord $record, Doctor $doctor, Tenant $tenant): SoapNote
    {
        return SoapNote::query()->firstOrCreate(
            [
                'medical_record_id' => $record->id,
                'doctor_id' => $doctor->id,
                'admission_id' => null,
            ],
            [
                'tenant_id' => $tenant->id,
                'subjective' => 'Control de rutina.',
                'objective' => 'Sin hallazgos relevantes.',
                'assessment' => 'Cribado de laboratorio.',
                'plan' => 'Solicitar exámenes.',
            ]
        );
    }

    private function labTest(Tenant $tenant, string $name, float $refMin, float $refMax, float $critMin, float $critMax): LabTest
    {
        return LabTest::query()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => $name],
            [
                'tenant_id' => $tenant->id,
                'name' => $name,
                'category' => 'Química',
                'unit' => 'mg/dL',
                'reference_min' => $refMin,
                'reference_max' => $refMax,
                'critical_min' => $critMin,
                'critical_max' => $critMax,
                'turnaround_min' => 60,
                'active' => true,
            ]
        );
    }

    private function pendingOrder(
        Tenant $tenant,
        Patient $patient,
        SoapNote $soapNote,
        MedicalRecord $record,
        User $medico,
        string $code,
        string $clinicalInfo
    ): LabOrder {
        return LabOrder::query()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => $code],
            [
                'tenant_id' => $tenant->id,
                'patient_id' => $patient->id,
                'soap_note_id' => $soapNote->id,
                'medical_record_id' => $record->id,
                'ordered_by' => $medico->id,
                'code' => $code,
                'priority' => 'rutina',
                'status' => 'pendiente',
                'clinical_info' => $clinicalInfo,
                'ordered_at' => now()->subDay(),
            ]
        );
    }

    private function sample(Tenant $tenant, LabOrder $order, User $labTech, string $barcode): Sample
    {
        return Sample::query()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'barcode' => $barcode],
            [
                'tenant_id' => $tenant->id,
                'lab_order_id' => $order->id,
                'received_by' => $labTech->id,
                'barcode' => $barcode,
                'sample_type' => 'sangre',
                'collected_at' => now()->subHours(20),
                'received_at' => now()->subHours(19),
                'status' => 'recibida',
            ]
        );
    }

    private function pendingItem(LabOrder $order, LabTest $test): LabOrderItem
    {
        return LabOrderItem::query()->firstOrCreate(
            ['lab_order_id' => $order->id, 'lab_test_id' => $test->id],
            ['status' => 'pendiente']
        );
    }
}