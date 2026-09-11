<?php

namespace App\Http\Controllers\LabResults;

use App\Application\LabResults\Command\CorregirResultadoCommand;
use App\Application\LabResults\Command\IngresarResultadoCommand;
use App\Application\LabResults\UseCase\ConsultarHistorialUseCase;
use App\Application\LabResults\UseCase\CorregirResultadoUseCase;
use App\Application\LabResults\UseCase\IngresarResultadoUseCase;
use App\Application\LabResults\UseCase\ListarResultadosPendientesUseCase;
use App\Domain\LabResults\Exception\DomainRuleException;
use App\Domain\LabResults\Exception\MotivoCorreccionInvalidoException;
use App\Domain\LabResults\Exception\MuestraNoAceptadaException;
use App\Domain\LabResults\Exception\MuestraNoEncontradaException;
use App\Domain\LabResults\Exception\MuestraRechazadaException;
use App\Domain\LabResults\Exception\ResultadoInexistenteException;
use App\Domain\LabResults\Exception\ResultadoSinCambiosException;
use App\Domain\LabResults\Exception\ResultadoYaRegistradoException;
use App\Domain\LabResults\Exception\TipoResultadoInvalidoException;
use App\Domain\LabResults\Exception\UnidadInvalidaException;
use App\Domain\LabResults\Model\VersionResultado;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

/**
 * API v2 del módulo (etapa 2): flujo versionado con Repository Pattern.
 *
 * Los endpoints ISP de la semana 2 permanecen intactos; este controlador
 * expone los casos de uso por rutas paralelas /api/v1/lab-results/v2.
 */
final class LabResultV2Controller extends \App\Http\Controllers\Controller
{
    public function pendientes(ListarResultadosPendientesUseCase $casoUso): JsonResponse
    {
        return response()->json(['data' => $casoUso->ejecutar()]);
    }

    public function store(Request $request, IngresarResultadoUseCase $casoUso): JsonResponse
    {
        $datos = $request->validate([
            'sample_id' => ['required', 'integer'],
            'result_type' => ['required', 'string', 'max:10'],
            'numeric_value' => ['nullable', 'numeric'],
            'text_value' => ['nullable', 'string', 'max:500'],
            'unit' => ['nullable', 'string', 'max:20'],
        ]);

        try {
            $version = $casoUso->ejecutar(new IngresarResultadoCommand(
                tenantId: $this->tenant($request),
                muestraId: (int) $datos['sample_id'],
                ingresadoPor: $this->actor($request),
                tipo: (string) $datos['result_type'],
                valorNumerico: isset($datos['numeric_value']) ? (string) $datos['numeric_value'] : null,
                valorTexto: $datos['text_value'] ?? null,
                unidad: $datos['unit'] ?? null,
            ));
        } catch (Throwable $e) {
            return $this->error($e);
        }

        return response()->json([
            'message' => 'Resultado ingresado como versión 1.',
            'data' => $this->serializar($version),
        ], 201);
    }

    public function corregir(Request $request, int $sampleId, CorregirResultadoUseCase $casoUso): JsonResponse
    {
        $datos = $request->validate([
            'motivo' => ['required', 'string', 'min:10', 'max:500'],
            'result_type' => ['required', 'string', 'max:10'],
            'numeric_value' => ['nullable', 'numeric'],
            'text_value' => ['nullable', 'string', 'max:500'],
            'unit' => ['nullable', 'string', 'max:20'],
        ]);

        try {
            $version = $casoUso->ejecutar(new CorregirResultadoCommand(
                tenantId: $this->tenant($request),
                muestraId: $sampleId,
                corregidoPor: $this->actor($request),
                motivo: (string) $datos['motivo'],
                tipo: (string) $datos['result_type'],
                valorNumerico: isset($datos['numeric_value']) ? (string) $datos['numeric_value'] : null,
                valorTexto: $datos['text_value'] ?? null,
                unidad: $datos['unit'] ?? null,
            ));
        } catch (Throwable $e) {
            return $this->error($e);
        }

        return response()->json([
            'message' => "Corrección registrada como versión {$version->numeroVersion}; la versión anterior se conserva.",
            'data' => $this->serializar($version),
        ], 201);
    }

    public function historial(Request $request, int $sampleId, ConsultarHistorialUseCase $casoUso): JsonResponse
    {
        try {
            $resultado = $casoUso->ejecutar($this->tenant($request), $sampleId);
        } catch (MuestraNoEncontradaException $e) {
            return response()->json(['error' => 'MUESTRA_NO_ENCONTRADA', 'detail' => $e->getMessage()], 404);
        }

        return response()->json([
            'data' => [
                'muestra_id' => $resultado['muestra_id'],
                'barcode' => $resultado['barcode'],
                'prueba_id' => $resultado['prueba']?->id,
                'prueba_nombre' => $resultado['prueba']?->name,
                'vigente' => $resultado['vigente'] !== null ? $this->serializar($resultado['vigente']) : null,
                'versiones' => array_map($this->serializar(...), $resultado['versiones']),
            ],
        ]);
    }

    /** @return array<string, mixed> */
    private function serializar(VersionResultado $v): array
    {
        return [
            'id' => $v->id,
            'version_number' => $v->numeroVersion,
            'muestra_id' => $v->muestraId,
            'contenido' => [
                'tipo' => $v->contenido->tipo->value,
                'valor_numerico' => $v->contenido->valorNumerico,
                'valor_texto' => $v->contenido->valorTexto,
                'unidad' => $v->contenido->unidad,
            ],
            'corrige_a_version' => $v->corrigeAVersion,
            'motivo_correccion' => $v->motivoCorreccion,
            'es_anormal' => $v->esAnormal,
            'es_critico' => $v->esCritico,
            'resulted_at' => $v->resultadaEn,
        ];
    }

    private function tenant(Request $request): string
    {
        /** @var \App\Models\Tenant|null $tenant */
        $tenant = $request->attributes->get('tenant') ?? app('currentTenant');

        if ($tenant === null) {
            abort(400, 'La cabecera X-Tenant-ID es obligatoria.');
        }

        return (string) $tenant->getKey();
    }

    private function actor(Request $request): int
    {
        $user = $request->user();

        if ($user === null) {
            abort(401, 'Autenticación requerida.');
        }

        return (int) $user->getAuthIdentifier();
    }

    private function error(Throwable $e): JsonResponse
    {
        [$codigo, $status] = match (true) {
            $e instanceof MuestraNoEncontradaException => ['MUESTRA_NO_ENCONTRADA', 404],
            $e instanceof MuestraRechazadaException => ['MUESTRA_RECHAZADA', 422],
            $e instanceof MuestraNoAceptadaException => ['MUESTRA_NO_ACEPTADA', 422],
            $e instanceof ResultadoYaRegistradoException => ['RESULTADO_YA_REGISTRADO', 409],
            $e instanceof ResultadoInexistenteException => ['RESULTADO_INEXISTENTE', 422],
            $e instanceof ResultadoSinCambiosException => ['SIN_CAMBIOS', 422],
            $e instanceof MotivoCorreccionInvalidoException => ['MOTIVO_INVALIDO', 422],
            $e instanceof TipoResultadoInvalidoException => ['TIPO_RESULTADO_INVALIDO', 422],
            $e instanceof UnidadInvalidaException => ['UNIDAD_INVALIDA', 422],
            $e instanceof DomainRuleException => ['REGLA_NEGOCIO', 422],
            default => ['ERROR_INTERNO', 500],
        };

        if ($codigo === 'ERROR_INTERNO') {
            report($e);
        }

        return response()->json(['error' => $codigo, 'detail' => $e->getMessage()], $status);
    }
}
