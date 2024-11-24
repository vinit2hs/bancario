<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BamImport implements ToModel,WithHeadingRow,SkipsEmptyRows
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Bam([
            //
        ]);
    }

    public function headingRow(): int
    {
        return 3;
    }

    public function isEmptyWhen(array $row): bool
    {
        $cosif = $row['cosif'];
        $lixo = preg_match('/TOTALIZADOR/',$cosif,$matches);
        if(empty($cosif) || $lixo){
            return true;
        }

        return false;
    }
}
