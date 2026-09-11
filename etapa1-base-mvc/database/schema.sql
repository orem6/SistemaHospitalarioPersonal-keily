-- ============================================================
-- ASII-19 · Ingreso de Resultados de Laboratorio
-- PRIMERA ETAPA (base/MVC en PHP vanilla)
-- Esquema mínimo: prueba, muestra, versión de resultado.
-- Todos los datos son FICTICIOS.
-- Las referencias a sistemas CENTRALES son únicamente UUID
-- lógicos (patient_ref / tenant_id): NO existe FK remota.
-- ============================================================

PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS lab_test_definitions (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    code         TEXT    NOT NULL UNIQUE,          -- código corto de la prueba
    name         TEXT    NOT NULL,
    result_type  TEXT    NOT NULL,                 -- NUMERICO | TEXTO
    unit         TEXT,                             -- unidad canónica (solo NUMERICO)
    created_at   TEXT    NOT NULL DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS samples (
    id               TEXT PRIMARY KEY,            -- UUID local de la muestra
    tenant_id        TEXT NOT NULL,               -- UUID lógico del hospital (escritura local)
    patient_ref      TEXT NOT NULL,               -- UUID lógico del paciente en el sistema CENTRAL (sin FK remota)
    barcode          TEXT NOT NULL UNIQUE,
    test_id          INTEGER NOT NULL REFERENCES lab_test_definitions(id),
    status           TEXT NOT NULL,               -- PENDIENTE | ACEPTADA | RECHAZADA
    rejection_reason TEXT,
    collected_at     TEXT,
    created_at       TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS lab_result_versions (
    id                    INTEGER PRIMARY KEY AUTOINCREMENT,
    sample_id             TEXT    NOT NULL REFERENCES samples(id),
    test_id               INTEGER NOT NULL REFERENCES lab_test_definitions(id),
    tenant_id             TEXT    NOT NULL,
    version_number        INTEGER NOT NULL,
    result_type           TEXT    NOT NULL,      -- NUMERICO | TEXTO
    numeric_value         REAL,
    text_value            TEXT,
    unit                  TEXT,
    corrected_from_version INTEGER,              -- versión que origina esta corrección (NULL = captura inicial)
    correction_reason     TEXT,                  -- motivo de la corrección (NULL = captura inicial)
    captured_at           TEXT    NOT NULL DEFAULT (datetime('now')),
    UNIQUE (sample_id, version_number)           -- las filas son inmutables: una corrección INSERTA, jamás UPDATEa
);

CREATE INDEX IF NOT EXISTS idx_samples_status ON samples(status);
CREATE INDEX IF NOT EXISTS idx_versions_sample ON lab_result_versions(sample_id, version_number);
