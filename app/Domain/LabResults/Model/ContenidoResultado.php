<?php

namespace App\Domain\LabResults\Model;

use App\Domain\LabResults\Exception\TipoResultadoInvalidoException;
use App\Domain\LabResults\Exception\UnidadInvalidaException;
use App\Domain\LabResults\Exception\ValorNumericoInvalidoException;
use App\Domain\LabResults\Exception\ValorTextoInvalidoException;

/**
 * Value Object inmutable con el CONTENIDO de un resultado
 * (tipo + valor + unidad), usado por la captura y cada corrección.
 *
 * Reglas que aplica este VO:
 *  - el tipo debe ser NUMERICO o TEXTO;
 *  - un resultado NUMERICO exige un número finito válido;
 *  - un resultado TEXTO exige texto no vacío (máx. 500 caracteres);
 *  - los resultados TEXTO no aceptan unidad.
 */
final readonly class ContenidoResultado
{
    public const MAX_LONGITUD_TEXTO = 500;

    private function __construct(
        public TipoResultado $tipo,
        public ?float $valorNumerico,
        public ?string $valorTexto,
        public ?string $unidad,
    ) {
    }

    public static function crear(
        string $tipoRaw,
        ?string $valorNumericoRaw,
        ?string $valorTextoRaw,
        ?string $unidadRaw,
    ): self {
        $tipoLimpio = strtoupper(trim($tipoRaw));
        $tipo = TipoResultado::tryFrom($tipoLimpio)
            ?? throw new TipoResultadoInvalidoException(
                "El tipo de resultado '{$tipoRaw}' no es válido; valores permitidos: NUMERICO, TEXTO."
            );

        $unidad = self::normalizarTexto($unidadRaw);

        return match ($tipo) {
            TipoResultado::Numerico => new self($tipo, self::parsearNumero($valorNumericoRaw), null, $unidad),
            TipoResultado::Texto => new self($tipo, null, self::parsearTexto($valorTextoRaw), self::rechazarUnidadEnTexto($unidad)),
        };
    }

    /** Rehidratación desde persistencia (datos ya validados al guardarse). */
    public static function desdeAlmacenamiento(
        TipoResultado $tipo,
        ?float $valorNumerico,
        ?string $valorTexto,
        ?string $unidad,
    ): self {
        return new self($tipo, $valorNumerico, $valorTexto, $unidad);
    }

    public function igualA(self $otro): bool
    {
        return $this->tipo === $otro->tipo
            && $this->valorNumerico === $otro->valorNumerico
            && $this->valorTexto === $otro->valorTexto
            && strcasecmp((string) $this->unidad, (string) $otro->unidad) === 0;
    }

    private static function parsearNumero(?string $raw): float
    {
        $limpio = self::normalizarTexto($raw);

        if ($limpio === null || !is_numeric($limpio)) {
            throw new ValorNumericoInvalidoException('El valor del resultado debe ser un número válido.');
        }

        $numero = (float) $limpio;

        if (!is_finite($numero)) {
            throw new ValorNumericoInvalidoException('El valor numérico no es finito y por lo tanto es inválido.');
        }

        return $numero;
    }

    private static function parsearTexto(?string $raw): string
    {
        $limpio = trim((string) $raw);

        if ($limpio === '') {
            throw new ValorTextoInvalidoException('El valor textual del resultado es obligatorio.');
        }

        if (mb_strlen($limpio) > self::MAX_LONGITUD_TEXTO) {
            throw new ValorTextoInvalidoException(
                sprintf('El valor textual no puede superar %d caracteres.', self::MAX_LONGITUD_TEXTO)
            );
        }

        return $limpio;
    }

    private static function rechazarUnidadEnTexto(?string $unidad): ?string
    {
        if ($unidad !== null && $unidad !== '') {
            throw new UnidadInvalidaException('Los resultados de tipo TEXTO no llevan unidad de medida.');
        }

        return null;
    }

    private static function normalizarTexto(?string $raw): ?string
    {
        $limpio = trim((string) $raw);

        return $limpio === '' ? null : $limpio;
    }
}
