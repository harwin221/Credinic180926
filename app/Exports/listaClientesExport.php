<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class listaClientesExport implements FromCollection,WithHeadings,ShouldAutoSize
{

    private $lista;

    public function __construct($list)
    {
        $this->lista=$list;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $datos = [];

        foreach ($this->lista as $prestamo){
            // Lógica de Clasificación corregida
            $letra = '-';
            if (in_array($prestamo->estado, [1, 3])) { // Activo o Vencido
                if (!$prestamo->fecha_atraso) {
                    $letra = 'A'; // Está al día
                } else {
                    $diasAtraso = \Carbon\Carbon::parse($prestamo->fecha_atraso)->diffInDays(\Carbon\Carbon::now());
                    if ($diasAtraso <= 15) $letra = 'A';
                    elseif ($diasAtraso <= 30) $letra = 'B';
                    elseif ($diasAtraso <= 60) $letra = 'C';
                    elseif ($diasAtraso <= 90) $letra = 'D';
                    else $letra = 'E';
                }
            }

            $aux = [];
            $aux[]=$prestamo->consecutivo;
            $aux[]=$prestamo->tipo_prestamo_texto;
            $aux[]=$prestamo->tipo_destino_texto;
            $aux[]=$prestamo->cliente_nombre;
            $aux[]=$prestamo->cliente_direccion;
            $aux[]=$prestamo->departamento_nombre." / ".$prestamo->municipio_nombre;
            $aux[]=$prestamo->telefono1 ." / ".$prestamo->telefono2;
            $aux[]=$prestamo->agente_nombre;
            $aux[]=$prestamo->vendedor_nombre;
            $aux[]=$prestamo->fecha_prestamo;
            $aux[]=$prestamo->fecha_desembolso;
            $aux[]='-'; // Fecha cancelado (requiere lógica extra si se desea)
            $aux[]=$prestamo->forma_pago;
            $aux[]=$prestamo->moneda;
            $aux[]=$prestamo->monto_prestamo;
            $aux[]=$prestamo->plazo_pago;
            $aux[]=$prestamo->tasa_prestamo;
            $aux[]=$prestamo->monto_cuota;
            $aux[]=$prestamo->interes_total_pagar;
            $aux[]=$prestamo->monto_financiado;
            $aux[]=$prestamo->total_cuotas ?? 0;
            $aux[]=$prestamo->estado_texto;
            $aux[]=$letra;
            $datos[]=$aux;
        }

        return new Collection($datos);
    }

    public function headings(): array
    {
        return [
            '# Desembolso',
            'Tipo',
            'Destino',
            'Cliente',
            'Dirección',
            'Departamento / Municipio',
            'Teléfono',
            'Cobrador',
            'Vendedor',
            'Fecha Creado',
            'Fecha Desembolso',
            'Fecha Cancelado',
            'Forma de Pago',
            'Moneda',
            'Total Capital',
            'Plazo (Meses)',
            'Tasa',
            'Total Cuota',
            'Total Interes',
            'Total Financiado',
            'Total Cuotas',
            'Estado',
            'Clasificacion',
        ];
    }
}
