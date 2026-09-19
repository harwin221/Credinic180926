<?php

namespace App\Http\Requests;

use App\utils;
use Illuminate\Foundation\Http\FormRequest;

class agenteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return \Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $id = $this->route()->parameter('id') ? decode($this->route()->parameter('id')) : null;

        return [
            'nombres'=>'required|string',
            'apellidos'=>'required|string',
            'cedula'=>'required|string|unique:users,cedula,'.$id,
            'telefono1'=>'numeric|min:8|nullable',
            'telefono2'=>'numeric|min:8|nullable',
            'direccion'=>'string|nullable'
        ];
    }

    public function messages()
    {
        return [
            'nombres.required'=>'El nombre es requerido',
            'apellidos.required'=>'El apellido es requerido',
            'cedula.unique'=>'La cédula ya se encuentra ingresada',
            'cedula.required'=>'La cédula es requerida',
            'telefono1.numeric'=>'El télefono solo debe contener números',
            'telefono2.numeric'=>'El télefono solo debe contener números'
        ];
    }
}
