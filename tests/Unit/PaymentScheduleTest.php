<?php

namespace Tests\Unit;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class PaymentScheduleTest extends TestCase
{
    private function callProtectedMethod(object $object, string $method, array $args = [])
    {
        $reflection = new ReflectionClass($object);
        $reflectionMethod = $reflection->getMethod($method);
        $reflectionMethod->setAccessible(true);

        return $reflectionMethod->invokeArgs($object, $args);
    }

    public function test_quincenal_preferred_days_alternates_between_preferred_and_complement()
    {
        $controller = new Controller();
        $fechaProgramada = Carbon::parse('2026-04-05');

        $nextFecha = $this->callProtectedMethod(
            $controller,
            'obtenerSiguienteFechaProgramada',
            [$fechaProgramada, null, '3', 15, 5]
        );

        $this->assertSame('2026-04-20', $nextFecha->toDateString());

        $nextNextFecha = $this->callProtectedMethod(
            $controller,
            'obtenerSiguienteFechaProgramada',
            [$nextFecha, null, '3', 15, 5]
        );

        $this->assertSame('2026-05-05', $nextNextFecha->toDateString());
    }

    public function test_holiday_adjustment_does_not_shift_subsequent_quincenal_schedule()
    {
        $controller = new Controller();
        $fechaProgramada = Carbon::parse('2026-05-01');
        $feriados = ['2026-05-01'];

        $fechaCuota = $this->callProtectedMethod(
            $controller,
            'ajustarFechaNoLaborable',
            [$fechaProgramada, $feriados]
        );

        $this->assertSame('2026-05-04', $fechaCuota->toDateString());

        $nextFechaProgramada = $this->callProtectedMethod(
            $controller,
            'obtenerSiguienteFechaProgramada',
            [$fechaProgramada, $fechaCuota, '3', 15, 1]
        );

        $this->assertSame('2026-05-16', $nextFechaProgramada->toDateString());
    }

    public function test_catorcenal_adds_14_days_without_using_adjusted_actual_date()
    {
        $controller = new Controller();
        $fechaProgramada = Carbon::parse('2026-04-05');
        $fechaCuotaAjustada = Carbon::parse('2026-04-06');

        $nextFechaProgramada = $this->callProtectedMethod(
            $controller,
            'obtenerSiguienteFechaProgramada',
            [$fechaProgramada, $fechaCuotaAjustada, '7', 14, 0]
        );

        $this->assertSame('2026-04-19', $nextFechaProgramada->toDateString());
    }
}
