<?php

namespace Tests\Unit;

use App\Repositories\AsistenciaRepository;
use App\Services\AforoService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** Nivel de aforo según el porcentaje de ocupación: libre < 50 %, moderado < 80 %, lleno desde 80 %. */
class NivelAforoTest extends TestCase
{
    public static function porcentajes(): array
    {
        return [
            'vacío' => [0, 'libre'],
            'justo debajo de 50' => [49, 'libre'],
            'en 50' => [50, 'moderado'],
            'justo debajo de 80' => [79, 'moderado'],
            'en 80' => [80, 'lleno'],
            'completo' => [100, 'lleno'],
            'sobre la capacidad' => [130, 'lleno'],
        ];
    }

    #[DataProvider('porcentajes')]
    public function test_nivel_segun_el_porcentaje(int $porcentaje, string $nivel): void
    {
        $this->assertSame($nivel, (new AforoService(new AsistenciaRepository))->nivel($porcentaje));
    }
}
