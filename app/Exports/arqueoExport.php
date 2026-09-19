<?php

namespace App\Exports;

use App\Models\abonosModel;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class arqueoExport implements FromCollection,ShouldAutoSize,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */

    private $arqueo;
    private $abonos;
    private $fecha;

    public function __construct($ar)
    {
        $this->arqueo = $ar;
        $this->fecha = $ar->fecha_arqueo;
        $abonos = abonosModel::whereDate('fecha_abono', $this->arqueo->fecha_arqueo)
            ->where('created_user_id', $this->arqueo->cobrador_id)
            ->get();

        $this->abonos = $abonos;
    }

    public function collection()
    {
        $abonosArr = [];
        $total_recuperado = 0;
        $auxAbo = [];
        $auxAbo[]=[
            '',
            '#',
            'Consecutivo',
            'Cliente',
            'Capital',
            'Interes',
            'Total Abonado',
        ];

        $abonosArr[] = $auxAbo;
        $auxAbo = [];

        foreach ($this->abonos as $ind => $ab) {
            $total_recuperado += $ab->total_abonado;

            $auxAbo = [
                '',
                strval($ind+1),
                $ab->prestamo->consecutivo,
                $ab->prestamo->cliente->full_name,
                $ab->total_abonado_capital,
                $ab->total_abonado_interes,
                $ab->total_abonado
            ];
            $abonosArr[] = $auxAbo;
        }

        $aux = [];
        $aux[]=[
            '',
            'Denominación C$',
            'Cantidad C$',
            'Total C$',
            '',
            'Denominación U$',
            'Cantidad U$',
            'Total U$',
        ];

        $aux[] = [
            '',
            "Moneda: 0.50",
            strval($this->arqueo->moneda_c_050),
            strval(($this->arqueo->moneda_c_050) * 0.50),
            '',
            "Billete: 1",
            strval($this->arqueo->billete_d_1),
            strval($this->arqueo->billete_d_1 * 1)
        ];

        $aux[] = [
            '',
            "Moneda: 1",
            strval($this->arqueo->moneda_c_1),
            strval(($this->arqueo->moneda_c_1) * 1),
            '',
            "Billete: 2",
            strval($this->arqueo->billete_d_2),
            strval($this->arqueo->billete_d_2 * 2)
        ];

        $aux[] = [
            '',
            "Moneda: 5",
            strval($this->arqueo->moneda_c_5),
            strval(($this->arqueo->moneda_c_5) * 5),
            '',
            "Billete: 5",
            strval($this->arqueo->billete_d_5),
            strval($this->arqueo->billete_d_5 * 5)
        ];

        $aux[] = [
            '',
            "Billete: 5 ",
            strval($this->arqueo->billete_c_5),
            strval(($this->arqueo->billete_c_5) * 5),
            '',
            "Billete: 10",
            strval($this->arqueo->billete_d_10),
            strval($this->arqueo->billete_d_10 * 10)
        ];

        $aux[] = [
            '',
            "Billete: 10",
            strval($this->arqueo->billete_c_10),
            strval(($this->arqueo->billete_c_10) * 10),
            '',
            "Billete: 20",
            strval($this->arqueo->billete_d_20),
            strval($this->arqueo->billete_d_20 * 20)
        ];


        $aux[] = [
            '',
            "Billete: 20",
            strval($this->arqueo->billete_c_20),
            strval(($this->arqueo->billete_c_20) * 20),
            '',
            "Billete: 50",
            strval($this->arqueo->billete_d_50),
            strval($this->arqueo->billete_d_50 * 50)
        ];

        $aux[] = [
            '',
            "Billete: 50",
            strval($this->arqueo->billete_c_50),
            strval(($this->arqueo->billete_c_50) * 50),
            '',
            "Billete: 100",
            strval($this->arqueo->billete_d_100),
            strval($this->arqueo->billete_d_100 * 100)
        ];

        $aux[] = [
            '',
            "Billete: 100",
            strval($this->arqueo->billete_c_100),
            strval(($this->arqueo->billete_c_100) * 100),
            '',
            '',
            '',
            '',
        ];

        $aux[] = [
            '',
            "Billete: 200",
            strval($this->arqueo->billete_c_200),
            strval(($this->arqueo->billete_c_200) * 200),
            '',
            '',
            '',
            '',
        ];

        $aux[] = [
            '',
            "Billete: 500",
            strval($this->arqueo->billete_c_500),
            strval(($this->arqueo->billete_c_500) * 500),
            '',
            '',
            '',
            '',
        ];


        $aux[] = [
            '',
            "Billete: 1000",
            strval($this->arqueo->billete_c_1000),
            strval(($this->arqueo->billete_c_1000) * 1000),
            '',
            '',
            '',
            '',
        ];

        $aux[] = [
            '',
            'SUMA TOTAL C$',
            '',
            strval($this->arqueo->total_cordoba),
            '',
            'SUMA TOTAL U$',
            '',
            strval($this->arqueo->total_dolar),

        ];


        $aux[] = [
            '',
            '',
            '',
            '',
            '',
            'Tipo Cambio: ',
            '',
            strval($this->arqueo->tipocambio),
        ];


        $aux[] = [
            '',
            '',
            '',
            '',
            '',
            'Total Conversión a C$',
            '',
            strval($this->arqueo->total_dolar * $this->arqueo->tipocambio),
        ];

        $aux[] = [
            '',
            'SUBTOTAL C$: ',
            strval($this->arqueo->total_cordoba+($this->arqueo->total_dolar * $this->arqueo->tipocambio)),
            '',
            '',
            '',
            '',
            '',
        ];

        $aux[] = [
            '',
            'DESEMBOLSOS C$: ',
            strval($this->arqueo->desembolsos ?? 0),
            '',
            '',
            '',
            '',
            '',
        ];

        $aux[] = [
            '',
            'TOTAL RECUPERADO C$',
            strval($total_recuperado),
            '',
            '',
            '',
            '',
            '',
        ];

        $aux[] = [
            '',
            'DIFERENCIA C$: ',
            strval($total_recuperado - ($this->arqueo->total_cordoba + ($this->arqueo->total_dolar * $this->arqueo->tipocambio) + ($this->arqueo->desembolsos ?? 0))),
            '',
            '',
            '',
            '',
            '',
        ];

        $aux[] = [
            '',
        ];
        $aux[] = [
            '',
        ];
        $aux[] = [
            '',
        ];

        $aux = array_merge($aux,$abonosArr);

        return new Collection($aux);
    }

    public function headings(): array
    {
        $arr[] = ['Arqueo correspondiente a: '.fecha_d_m_Y($this->fecha)];
        $arr[] = ['Cobrador: '.$this->arqueo->cobrador->full_name];
        return $arr;
    }
}
