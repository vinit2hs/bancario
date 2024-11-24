<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DmsImport implements ToCollection,WithHeadingRow,SkipsEmptyRows
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        //
    }

    public function headingRow(): int
    {
        return 3;
    }

    public function isEmptyWhen(array $row): bool
    {
        $cosif = $row['cosif'];
        if(empty($cosif)){
            return true;
        }

        return false;
    }
}
