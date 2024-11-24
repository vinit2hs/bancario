<?php

namespace App\Http\Controllers;

use App\Classes\Processamentos;
use App\Imports\DmsImport;
use App\Models\BamProcessados;
use App\Models\DmsProcessador;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ProcessamentoDmsController extends Controller
{
    public function index()
    {

        $disk = 'public';

        $path_bam = Storage::disk($disk)->path('');
        $path_bam_processados = "DMS_PROCESSADOS/";
        $files_bam = Storage::disk($disk)->files('DMS');

        foreach ($files_bam as $file) {

            list($mes,$ano) = Processamentos::getMesAno($file);

            $path = $path_bam.$file;
            $bam = Excel::toArray(new DmsImport(),$path);
            $bam = current($bam);

            $quant = count($bam);

            foreach ($bam as $key => $item) {

                $key = $key + 1;

                $cosif = $item['cosif'];
                $descricao = $item['descricao'];
                $rubrica = $item['conta'];
                $item_lei = $item['item_lei'];
                $saldo_anterior = $item['santerior'];
                $debito = $item['debitos'];
                $credito = $item['credito'];
                $saldo_atual = $item['satual'];
                $receita = $item['receita'];
                $aliquota = $item['aliquota'];
                $issqn = $item['issqn'];


                $data = [
                    'mes' => $mes,
                    'ano' => $ano,
                    'cosif' => $cosif,
                    'rubrica' => $rubrica,
                    'descricao_rubrica' => $descricao,
                    'item_lei' => $item_lei,
                    'saldo_anterior' => $saldo_anterior,
                    'debito' => $debito,
                    'credito' => $credito,
                    'saldo_atual' => $saldo_atual,
                    'receita_tributavel' => $receita,
                    'aliquota' => $aliquota,
                    'issqn' => $issqn,
                ];

                DB::beginTransaction();

                try {

                    $find = DmsProcessador::where('rubrica',$rubrica)
                        ->where('mes',$mes)
                        ->where('ano',$ano)
                        ->first();
                    if ($find && $quant != $key){
                        continue;
                    }

                    DmsProcessador::create($data);
                    DB::commit();
                }catch (\Exception $e){
                    DB::rollBack();
                    print_r($data);exit('error');
                }

                if ($quant == $key){
                    $path_to_move = $path_bam_processados.basename($file);
                    Storage::disk($disk)->move($file,$path_to_move);
                }


            }


            //echo $file;exit();
        }

    }
}
