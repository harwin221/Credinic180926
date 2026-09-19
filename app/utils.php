<?php

namespace App;

use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class utils
{
    static function saveOrUpdatePhoto(&$request, $directory,$fotoOriginalName)
    {
        $image = Image::make($request->file('foto'));
        if ($fotoOriginalName == 'no-photo.jpg')
            $imageName = Str::random(16) . "." . $request->file('foto')->extension();
        else {
            $nombreFoto = explode('.', $fotoOriginalName);
            $imageName = $nombreFoto[0] . "." . $request->file('foto')->extension();
        }
        $destino = public_path($directory);
        $image->resize(200, 200, function ($constriant) {
            $constriant->aspectRatio();
        });
        $image->save($destino . $imageName);

        return $imageName;
    }
}
