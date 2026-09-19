<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class usersFiadoresModel extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'users_fiadores';

    public function getIdEncAttribute()
    {
        return encode($this->id);
    }

    function fiador(){
        return $this->hasOne(User::class,'id','user_fiador_id');
    }

    function user(){
        return $this->hasOne(User::class,'id','user_id');
    }

    function getFullNameAttribute(){
        return $this->fiador->fullname;
    }

    function getIdEncFiadorAttribute(){
        return encode($this->user_fiador_id);
    }
}
