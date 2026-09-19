<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class estadoCuentaClienteExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    private $cliente;
    private $prestamos;

    public function __construct($clie, $pres)
    {
        $this->cliente = $clie;
        $this->prestamos = $pres;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $data = [];

        $aux = [];
        $aux[] = $this->cliente->full_name;
        $aux[] = $this->cliente->cedula;
        $aux[] = $this->cliente->direccion;
        $aux[] = $this->cliente->telefono1 . " " . $this->cliente->telefono2;
        $data[] = $aux;

        $aux = [];
        $aux[] = '';
        $data[] = $aux;

        foreach ($this->prestamos as $pres) {

            $aux = [];
            $aux[] = $pres->fecha_prestamo;
            $aux[] = $pres->monto_prestamo;
            $aux[] = $pres->monto_financiado;
            $aux[] = $pres->monto_financiado;
            $aux[] = $pres->interes_total_pagar;
            $aux[] = $pres->fecha_primer_pago;
            $aux[] = $pres->cuotas()->orderBy('id', 'desc')->first() ? fecha_d_m_Y($pres->cuotas()->orderBy('id', 'desc')->first()->fecha_cuota) : "-";
            $aux[] = $pres->plazo_pago;
            $aux[] = $pres->tasa_prestamo;
            $aux[] = $pres->forma_pago;
            $data[] = $aux;

            $aux = [];
            $aux[] = '';
            $data[] = $aux;

            $aux = [];
            $aux[] = '';
            $data[] = $aux;
        }
        return new Collection($data);
    }

    public function headings(): array
    {
        $headers = [];

        $aux = [];
        $aux[] = 'Cliente';
        $aux[] = 'Cédula';
        $aux[] = 'Dirección';
        $aux[] = 'Teléfonos';
        $headers[] = $aux;

        $aux = [];
        $aux[] = 'Fecha Préstamo';
        $aux[] = 'Monto Préstamo';
        $aux[] = 'Monto Financiado';
        $aux[] = 'Monto Intereses';
        $aux[] = 'Fecha Primer Pago';
        $aux[] = 'Fecha Ultima Cuota';
        $aux[] = 'Plazo del Préstamo';
        $aux[] = 'Tasa del Préstamo';
        $aux[] = 'Tipo';
        $headers[] = $aux;

        return $headers;
    }
}
