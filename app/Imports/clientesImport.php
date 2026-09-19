<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class clientesImport implements ToCollection,WithCustomCsvSettings
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        return $collection;
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ";"
        ];
    }

}
