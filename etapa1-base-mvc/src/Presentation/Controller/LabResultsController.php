<?php

declare(strict_types=1);

namespace LabResults\Presentation\Controller;

use LabResults\AppContainer;
use LabResults\Application\Command\CorregirResultadoCommand;
use LabResults\Application\Command\IngresarResultadoCommand;

/**
 * CAPA PRESENTATION (Controlador).
 * - valida datos BÁSICOS de entrada (presencia);
 * - invoca los casos de uso;
 * - presenta el resultado.
 * NO contiene SQL ni reglas de negocio.
 */
final class LabResultsController
{
    public function __construct(private readonly AppContainer $contenedor)
    {
    }

    /** Cola de trabajo: muestras aceptadas pendientes de captura. */
    public function pendientes(): void
    {
        $pendientes = $this->contenedor->listarPendientes()->ejecutar();
        $todas = $this->contenedor->muestras->listarTodas();

        require __DIR__ . '/../View/layout.php';
        require __DIR__ . '/../View/pendientes.php';
    }

    public function formularioIngreso(string $muestraId): void
    {
        $pendientes = $this->contenedor->listarPendientes()->ejecutar();
        $seleccionada = null;

        foreach ($pendientes as $item) {
            if ($item['muestra_id'] === $muestraId) {
                $seleccionada = $item;
                break;
            }
        }

        require __DIR__ . '/../View/layout.php';
        require __DIR__ . '/../View/form_ingreso.php';
    }

    public function ingresar(array $post): void
    {
        // Validación básica de presencia (las reglas profundas viven en Domain).
        $errores = [];
        if (($post['muestra_id'] ?? '') === '') {
            $errores[] = 'Debe seleccionar una muestra.';
        }
        if (trim((string) ($post['tipo'] ?? '')) === '') {
            $errores[] = 'El tipo de resultado es obligatorio.';
        }

        if ($errores !== []) {
            $this->renderErrores($errores, '?action=form-ingreso&muestra=' . urlencode((string) ($post['muestra_id'] ?? '')));
            return;
        }

        $version = $this->contenedor->ingresarResultado()->ejecutar(
            new IngresarResultadoCommand(
                muestraId: (string) $post['muestra_id'],
                tipo: (string) $post['tipo'],
                valorNumerico: $post['valor_numerico'] ?? null,
                valorTexto: $post['valor_texto'] ?? null,
                unidad: $post['unidad'] ?? null,
            )
        );

        header('Location: ?action=historial&muestra=' . urlencode($version->sampleId) . '&ok=ingreso');
    }

    public function formularioCorreccion(string $muestraId): void
    {
        $historial = $this->contenedor->consultarHistorial()->ejecutar($muestraId);

        require __DIR__ . '/../View/layout.php';
        require __DIR__ . '/../View/form_correccion.php';
    }

    public function corregir(array $post): void
    {
        $errores = [];
        if (($post['muestra_id'] ?? '') === '') {
            $errores[] = 'Debe indicar la muestra a corregir.';
        }
        if (trim((string) ($post['motivo'] ?? '')) === '') {
            $errores[] = 'El motivo de la corrección es obligatorio.';
        }

        if ($errores !== []) {
            $this->renderErrores($errores, '?action=form-correccion&muestra=' . urlencode((string) ($post['muestra_id'] ?? '')));
            return;
        }

        $version = $this->contenedor->corregirResultado()->ejecutar(
            new CorregirResultadoCommand(
                muestraId: (string) $post['muestra_id'],
                motivo: (string) $post['motivo'],
                tipo: (string) $post['tipo'],
                valorNumerico: $post['valor_numerico'] ?? null,
                valorTexto: $post['valor_texto'] ?? null,
                unidad: $post['unidad'] ?? null,
            )
        );

        header('Location: ?action=historial&muestra=' . urlencode($version->sampleId) . '&ok=correccion');
    }

    public function historial(string $muestraId): void
    {
        $historial = $this->contenedor->consultarHistorial()->ejecutar($muestraId);

        require __DIR__ . '/../View/layout.php';
        require __DIR__ . '/../View/historial.php';
    }

    private function renderErrores(array $errores, string $volverA): void
    {
        echo "<!doctype html><meta charset='utf-8'><title>Datos incompletos</title>"
            . "<body style='font-family:sans-serif;background:#fffbeb;padding:2rem'>"
            . '<h1 style="color:#b45309">Revise los datos</h1><ul>';

        foreach ($errores as $error) {
            echo '<li>' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '</li>';
        }

        echo "</ul><a href='" . htmlspecialchars($volverA, ENT_QUOTES, 'UTF-8') . "'>&larr; Volver</a></body>";
    }
}
