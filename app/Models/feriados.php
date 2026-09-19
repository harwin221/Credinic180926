<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class feriados extends Model
{
    use HasFactory;
    protected $table = 'feriados';
    public $timestamps = false;

    public function getIdEncAttribute()
    {
        return encode($this->id);
    }

    public function getTipoFechaAttribute()
    {
        if ($this->tipo == 1)
            return 'Recurrente';
        else
            return 'No recurrente';
    }
}
