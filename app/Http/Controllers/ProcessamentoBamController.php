<?php

namespace App\Http\Controllers;

use App\Classes\Processamentos;
use App\Imports\BamImport;
use App\Models\BamProcessados;
use App\Models\Cosifs;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ProcessamentoBamController extends Controller
{
    public function index()
    {

        $disk = 'public';

        $path_bam = Storage::disk($disk)->path('');
        $path_bam_processados = "BAM_PROCESSADOS/";
        $files_bam = Storage::disk($disk)->files('BAM');

        foreach ($files_bam as $file) {

            list($mes,$ano) = Processamentos::getMesAno($file);

            $path = $path_bam.$file;
            $bam = Excel::toArray(new BamImport(),$path);
            $bam = current($bam);

            $quant = count($bam);

            foreach ($bam as $key => $item) {

                $key = $key + 1;

                $cosif = $item['cosif'];
                $descricao = $item['descricao'];
                $rubrica = $item['rubrica'];
                $saldo_anterior = $item['saldo_anterior'];
                $debito = $item['debito'];
                $credito = $item['credito'];
                $saldo_atual = $item['saldo_atual'];


                $data = [
                    'mes' => $mes,
                    'ano' => $ano,
                    'cosif' => $cosif,
                    'rubrica' => $rubrica,
                    'descricao_rubrica' => $descricao,
                    'saldo_anterior' => $saldo_anterior,
                    'debito' => $debito,
                    'credito' => $credito,
                    'saldo_atual' => $saldo_atual,
                ];

                DB::beginTransaction();

                try {

                    $find = BamProcessados::where('rubrica',$rubrica)
                        ->where('mes',$mes)
                        ->where('ano',$ano)
                        ->first();
                    if ($find && $quant != $key){
                        continue;
                    }

                    BamProcessados::create($data);
                    DB::commit();
                    if ($quant == $key){
                        $path_to_move = $path_bam_processados.basename($file);
                        Storage::disk($disk)->move($file,$path_to_move);
                    }
                }catch (\Exception $e){
                    DB::rollBack();
                    print_r($data);exit('error');
                }


            }


            //echo $file;exit();
        }



    }
}
