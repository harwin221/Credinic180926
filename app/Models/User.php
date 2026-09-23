<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes,HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'nombres',
        'apellidos',
        'direccion',
        'telefono1',
        'telefono2',
        'cedula',
        'foto',
        'sexo',
        'estado_civil',
        'email',
        'password',
        'estado',
        'dep_mun',
        'sucursal_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function isOnline()
    {
        return \Cache::has('user-is-online-' . $this->id);
    }

    public function getTipoUsuarioActualAttribute()
    {
        if ($this->tipo_usuario == 1)
            return "Administrador";
        elseif ($this->tipo_usuario == 2)
            return "Administrativo";
        elseif ($this->tipo_usuario == 4)
            return "Agente";
        elseif ($this->tipo_usuario == 6)
            return "Cliente Nuevo";
    }

    public function getIdEncAttribute()
    {
        return encode($this->id);
    }

    public function getFullNameAttribute()
    {
        return "{$this->nombres} {$this->apellidos}";
    }

        public function getFullNameWithUsernameAttribute()
    {
        return "{$this->nombres} {$this->apellidos} ( {$this->username} )";
    }

    public function getUserImageAttribute()
    {
        $img = asset('assets/img/');
        if ($this->tipo_usuario == 2)//admin
            $img .= "/adminFotos/" . $this->foto;
        elseif ($this->tipo_usuario == 3 || $this->tipo_usuario == 6)//clientes
            $img .= "/clientesFotos/" . $this->foto;
        elseif ($this->tipo_usuario == 4)//agentes
            $img .= "/agentesFotos/" . $this->foto;
        elseif ($this->tipo_usuario == 5)//agentes
            $img .= "/fiadorFotos/" . $this->foto;
        else
            $img.="/no-photo.jpg"; //administrador

        return $img;
    }

    public function getUserImageDirectoryAttribute()
    {
        $img = "";
        if ($this->tipo_usuario == 2)//admin
            $img .= "assets/img/adminFotos/";
        if ($this->tipo_usuario == 3 || $this->tipo_usuario == 6)//clientes
            $img .= "assets/img/clientesFotos/";
        if ($this->tipo_usuario == 4)//agentes
            $img .= "assets/img/agentesFotos/";
        if ($this->tipo_usuario == 5)//agentes
            $img .= "assets/img/fiadorFotos/";
        return $img;
    }

    public function getEstadoUserAttribute()
    {
        $estado = [];
        switch ($this->estado) {
            case 1:
                $estado = ['text' => 'Activo', 'color' => 'bg-success'];
                break;
            case 2:
                $estado = ['text' => 'Inactivo', 'color' => 'bg-secondary'];
                break;
            case 3:
                $estado = ['text' => 'Pendiente', 'color' => 'bg-primary'];
                break;
            case 4:
                $estado = ['text' => 'Aprobado', 'color' => 'bg-info'];
                break;
            case 5:
                $estado = ['text' => 'Rechazado', 'color' => 'bg-warning'];
                break;
        }
        return $estado;
    }

    public function getIsAdminAttribute()
    {
        return $this->tipo_usuario == 1;
    }

    public function getNegociosClienteArrAttribute()
    {
        return $this->user_negocios->pluck('nombre', 'id_enc')->toArray();
    }

    public function getEstadoCivilUserAttribute()
    {
        $estados = [''=>'N/D','0'=>'Soltero(a)','1'=>'Casado(a)','2'=>'Unión Libre','3'=>'Viudo(a)','4'=>'Divorciado(a)'];
        return $estados[$this->estado_civil];
    }

    public function scopeBuscar($query, $buscar)
    {
        $query->when($buscar, function ($query) use ($buscar) {
            $query->where(function ($query2) use ($buscar) {
                $query2->where('nombres', 'like', '%' . $buscar . '%')
                    ->orwhere('apellidos', 'like', '%' . $buscar . '%')
                    ->orwhere('cedula', 'like', '%' . $buscar . '%');
            });
        });
    }

    function scopeAdmin($query){
        return $query->where('tipo_usuario',2);
    }

    function scopeCliente($query){
        return $query->where('tipo_usuario',3);
    }

    function scopeAgente($query){
        return $query->where('tipo_usuario',4);
    }

    function scopeFiador($query){
        return $query->where('tipo_usuario',5);
    }

    function scopeCnuevo($query)
    {
        return $query->where('tipo_usuario', 6);
    }

    function scopeActivo($query){
        return $query->where('estado',1);
    }

    public function user_negocios()
    {
        return $this->hasMany(userNegociosModel::class,'user_id','id');
    }

    public function prestamos()
    {
        return $this->hasMany(prestamosModel::class,'user_id','id')->orderBy('id','desc');
    }

    public function documentosUser()
    {
        return $this->hasMany(userDocumentosModel::class,'user_id','id');
    }

    public function fiadores()
    {
        return $this->hasMany(usersFiadoresModel::class,'user_id','id');
    }

    public function clientesAsignados()
    {
        return $this->hasMany(prestamosModel::class,'agente_id','id');
    }

    public function agentesAsignado()
    {
        return $this->hasMany(userAsignadoModel::class, 'user_id', 'id');
    }

    public function fiadorClientes()
    {
        return $this->hasMany(usersFiadoresModel::class,'user_fiador_id','id');
    }

    public function getFiadorClienteAttribute()
    {
        return usersFiadoresModel::where('user_fiador_id', $this->id)->first();
    }

    public function departamento_municipio()
    {
        return $this->hasOne(departamentoMunicipioModel::class,'id','dep_mun');
    }

    public function solicitudes()
    {
        return $this->hasMany(solicitudPrestamoModel::class,'user_id','id')->orderBy('id','desc');
    }

    public function getUserCreateAttribute()
    {
        return User::where('id',$this->created_user_id)->first();
    }

    public function sucursal()
    {
        return $this->belongsTo(sucursalModel::class, 'sucursal_id', 'id');
    }

    // Obtener la sucursal del admin, derivada de la cadena para agentes/clientes
    public function getSucursalDerivedAttribute()
    {
        // Si es admin, tiene sucursal directa
        if ($this->tipo_usuario == 2) {
            return $this->sucursal;
        }
        // Si es agente, buscar su admin asignado y traer la sucursal de ese admin
        if ($this->tipo_usuario == 4) {
            $asignado = userAsignadoModel::where('admin_asignado_id', $this->id)->first();
            if ($asignado) {
                $admin = User::find($asignado->user_id);
                return $admin ? $admin->sucursal : null;
            }
        }
        return null;
    }

    // Mutators to always store in UPPERCASE (UTF-8 safe)
    public function setNombresAttribute($value)
    {
        $this->attributes['nombres'] = $value !== null ? mb_strtoupper($value, 'UTF-8') : null;
    }

    public function setApellidosAttribute($value)
    {
        $this->attributes['apellidos'] = $value !== null ? mb_strtoupper($value, 'UTF-8') : null;
    }

    public function setCedulaAttribute($value)
    {
        $this->attributes['cedula'] = $value !== null ? mb_strtoupper($value, 'UTF-8') : null;
    }

    public function setDireccionAttribute($value)
    {
        $this->attributes['direccion'] = $value !== null ? mb_strtoupper($value, 'UTF-8') : null;
    }
}
