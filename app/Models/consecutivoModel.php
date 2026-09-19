<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class consecutivoModel extends Model
{
    use HasFactory;
    protected $table = 'consecutivos';

    protected $fillable = ['valor'];

    public static function obtenerConsecutivo()
    {
        return DB::transaction(function () {
            $consecutivo = consecutivoModel::lockForUpdate()->latest()->first();

            if (!$consecutivo) {
                $consecutivo = consecutivoModel::create(['valor' => 1]);
            } else {
                $consecutivo->update(['valor' => $consecutivo->valor + 1]);
            }

            return $consecutivo->valor;
        });
    }
}
