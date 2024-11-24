<?php

namespace App\Classes;

use App\Imports\EstbanImport;
use App\Models\Auditorias;
use App\Models\BamProcessados;
use App\Models\Cosifs;
use App\Models\CosifsContas;
use App\Models\DmsProcessador;
use App\Models\Estiban;
use App\Models\Jobs;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Queue\Jobs\Job;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Excel;
use Spatie\SimpleExcel\SimpleExcelReader;
use ZanySoft\Zip\Facades\Zip;

class Processamentos
{

    public $idMunicipio;

    public $indiceMunicipio;

    public $aliquotaMunicipio;

    public $multaMunicipio;

    public $jurosMunicipio;

    public $auditoria;

    public $cosifs;

    public $listCosifs;

    public $mes;

    public $ano;

    public function __construct(){

    }

    public function getAuditoria($id){

        $findAuditoria = Auditorias::where('id',$id)
            ->where('status',0)
            ->first();
        if (empty($findAuditoria)){
            throw new \Exception("Auditoria não encontrada ou já foi concluida!");
        }

        $this->mes = $findAuditoria->mes;
        $this->ano = $findAuditoria->ano;
        $this->auditoria = $findAuditoria;
    }
    public function setDadosMunicipio(){

        $findAuditorias = $this->auditoria;

        $this->idMunicipio = $findAuditorias->agencia->cidade->id;
        $this->indiceMunicipio = $findAuditorias->agencia->cidade->indice;
        $this->aliquotaMunicipio = $findAuditorias->agencia->cidade->aliquota;
        $this->multaMunicipio = $findAuditorias->agencia->cidade->multa;
        $this->jurosMunicipio = $findAuditorias->agencia->cidade->juros;

    }

    public function getListcosifs(){
        $cosifs = Cosifs::get();
        $cosifs = $cosifs->map(function ($item, $key) {
            $this->listCosifs[] = $item->cosif;
            $this->cosifs[$item->cosif] = $item;
        });
        $dms = DmsProcessador::select('cosif')->distinct('cosif')->where('mes',$this->mes)
            ->where('ano',$this->ano)
            ->get()->toArray();

        foreach ($dms as $key => $item) {
            $cosif = $item['cosif'];
            if (!in_array($cosif,$this->listCosifs)){
                $this->listCosifs[] = $cosif;
            }
        }

    }

    public function getBam(){
        $bam = BamProcessados::whereIn('cosif',$this->listCosifs)
            ->where('mes',$this->auditoria->mes)
            ->where('ano',$this->auditoria->ano)
            ->get();
    }

    public function createFromRange($dataInicial,$dataFinal,$idAgencia){
        $period = CarbonPeriod::create($dataInicial, '1 month', $dataFinal);

        DB::beginTransaction();

        foreach ($period as $date) {
            try {
                $mes = $date->format('m');
                $ano = $date->format('Y');
                $createAuditoria = Auditorias::create([
                    'id_agencia' => $idAgencia,
                    'mes' => $mes,
                    'ano' => $ano,
                    'status' => 0
                ]);
                $createEstiban = Estiban::create([
                    'id_auditoria' => $createAuditoria->id,
                    'status' => 0
                ]);
                DB::commit();
            }catch (\Exception $e){
                DB::rollBack();
                throw new \Exception("Erro ao processar o periodo");
            }


        }
    }

    public function processEstban(){
        //$periodo = $this->ano.$this->mes;
        $estban = Estiban::where('status',0)->first();
        if (empty($estban)){
            exit();
        }
        $periodo = $estban->auditoria->ano.$estban->auditoria->mes;
        $filename = "{$periodo}_ESTBAN_AG.ZIP";
        $url = "https://www4.bcb.gov.br/fis/cosif/cont/estban/agencia/{$filename}";

        $tempName = tempnam(sys_get_temp_dir(), 'estban').'.zip';
        $response = Http::sink($tempName)->get($url);
        if (!$response->ok()){
            throw new \Exception("Erro ao baixar o arquivo: ".$response->reason());
        }
        Storage::disk('public')->putFileAs("ESTBAN",$tempName,$filename);
        $zipFile = Storage::disk('public')->path('ESTBAN/'.$filename);

        $zip = Zip::open($zipFile);
        $zip->extract(Storage::disk('public')->path('ESTBAN/'.$periodo));

        $files = Storage::disk('public')->files('ESTBAN/'.$periodo);

        $csv = Storage::disk('public')->path($files[0]);

        $rows = SimpleExcelReader::create($csv)
            ->useDelimiter(';')
            ->trimHeaderRow('#')
            ->headerOnRow(2)
            ->getRows()
            ->filter(function(array $rowProperties) {
                $cod_bacen = $this->auditoria->agencia->cidade->bacen;
                $cod_agencia = $this->auditoria->agencia->cnpj;
                $agencia = (int)trim($rowProperties['AGENCIA'],"'");
                if ($rowProperties['CODMUN'] == $cod_bacen && $agencia == $cod_agencia){
                    return true;
                }
                return false;
            })
            ->each(function(array $rowProperties) use ($estban) {
                $total = $rowProperties['VERBETE_711_CONTAS_CREDORAS'];
                DB::beginTransaction();
                try {
                    $estban->update([
                        'valor_711' => $total,
                        'status' => 1
                    ]);
                    DB::commit();
                }catch (\Exception $e){
                    DB::rollBack();
                    echo "Erro ao processar o estban da auditoria ".$this->auditoria->id;
                }
            });


    }

    public function calcContasCredoras(){
        $auditoria = Auditorias::whereNull('total_contas_credoras')->first();
        if (empty($auditoria)){
            exit();
        }
        $estban = Estiban::where('id_auditoria',$auditoria->id)->first();
        $valor_711 = (float)$estban->valor_711;
        $mes = $auditoria->mes;
        $ano = $auditoria->ano;
        $bam = BamProcessados::where('mes',$mes)
            ->where('ano',$ano)
            ->where('cosif','like',"7%")
            ->get();
        $total = 0;
        foreach ($bam as $item) {
            $saldo_atual = (float)$item->saldo_atual;
            $total = $total + $saldo_atual;
        }

        DB::beginTransaction();

        try {

            $auditoria->update([
                'total_contas_credoras' => $total,
                'diferenca_estban' => $valor_711 - $total
            ]);

            DB::commit();

        }catch (\Exception $e){
            DB::rollBack();
            print_r($auditoria);exit();
        }
    }

    public function newJob($mes,$ano){
        $rows = BamProcessados::where('mes',$mes)
            ->where('ano',$ano)
            ->whereIn('cosif',$this->listCosifs)
            ->get();

        DB::beginTransaction();

        foreach ($rows as $bam){

            $id_auditoria = $this->auditoria->id;
            $id_bam = $bam->id;
            $id_dms = 0;
            try {
                $id_cosif = $this->cosifs[$bam->cosif]->id;
            }catch (\Exception $e){
                continue;
            }

            $id_indice = $this->indiceMunicipio->id;
            $id_cidade = $this->idMunicipio;
            $cosif = $bam->cosif;
            $rubrica = $bam->rubrica;
            $base_calculo = $bam->credito - $bam->debito;
            $aliquota = $this->aliquotaMunicipio;
            $multa = $this->multaMunicipio;
            $iss_apurado = 0;
            $diferenca_iss = 0;
            $iss_atualizado_indice = 0;
            $iss_atualizado_multa = 0;
            $status = 0;

            $findConta = CosifsContas::where('cosif',$cosif)
                ->where('conta',$rubrica)
                ->first();

            if (!$findConta){
                continue;
            }

            $findJob = Jobs::where('id_auditoria',$id_auditoria)
                ->where('id_cosif',$id_cosif)
                ->where('rubrica',$rubrica)
                ->first();

            if ($findJob){
                continue;
            }

            $dms = DmsProcessador::where('mes',$mes)
                ->where('ano',$ano)
                ->where('cosif',$cosif)
                ->where('rubrica',$rubrica)
                ->first();

            if ($dms){
                $id_dms = $dms->id;
            }

            if ($base_calculo < 0){
                $base_calculo = 0;
            }


            $data = [
                'id_auditoria' => $this->auditoria->id,
                'id_bam' => $id_bam,
                'id_dms' => $id_dms,
                'id_cosif' => $id_cosif,
                'id_indice' => $id_indice,
                'id_cidade' => $id_cidade,
                'rubrica' => $rubrica,
                'base_calculo' => $base_calculo,
                'aliquota' => $aliquota,
                'multa' => $multa,
                'item' => $findConta->item
            ];

            try {
                Jobs::create($data);
                DB::commit();
            }catch (\Exception $e){
                DB::rollBack();
                print_r($data);exit();
            }



        }

    }

    protected function calcIss($base_calculo,$aliquota){
        return $this->numberPrecision($base_calculo * ($aliquota / 100),2);
    }

    protected function numberPrecision($number, $decimals = 0)
    {
        $negation = ($number < 0) ? (-1) : 1;
        $coefficient = 10 * $decimals;
        return $negation * floor((string)(abs($number) * $coefficient)) / $coefficient;
    }

    public function proccessJob($idAuditoria){

        $jobs = Jobs::where('id_auditoria',$idAuditoria)
            ->where('status',0)
            ->get();

        if (empty($jobs->count())){
            Auditorias::where('id',$idAuditoria)->update([
                'status' => 1
            ]);
            //throw new \Exception("Todos as linhas dessa auditoria {$idAuditoria} já foram processadas");
            exit('tudo processado');
        }

        foreach ($jobs as $job) {
            $bam = $job->bam;
            $dms = $job->dms;

            $base_calculo = (float)$job->base_calculo;
            $aliquota = (float)$job->aliquota;
            $iss_apurado = 0;
            $iss_recolhido = 0;
            $diferenca_iss = 0;
            $iss_atualizado_indice = 0;
            $iss_atualizado_multa = 0;
            $iss_atualizado_juros = 0;

            if ($dms){
                $iss_recolhido = (float)$dms->issqn;
            }

            $calcIss = $this->calcIss($base_calculo,$aliquota);
            $iss_apurado = $calcIss;

            if ($iss_recolhido < $iss_apurado){
                $diferenca_iss = (float)$iss_apurado - $iss_recolhido;
            }

            /*if ($iss_apurado < $calcIss){
                $diferenca_iss = (float)$calcIss - $iss_apurado;
            }*/

            // if ($this->auditoria->valor_dam <= 0){
            //     $diferenca_iss = (float)$calcIss;
            //     $iss_recolhido = 0;
            // }

            if ($diferenca_iss > 0){
                $iss_atualizado_indice = round($diferenca_iss * ($job->auditoria->fator_atualizacao - 1),3);
                $iss_atualizado_multa = round(($iss_atualizado_indice + $diferenca_iss) * ($this->multaMunicipio/100),3);
                $iss_atualizado_juros = $this->calcJuros(
                    $diferenca_iss,
                    $this->jurosMunicipio,
                    self::diferencaMeses($job->auditoria->mes.'/'.$job->auditoria->ano,'06/2023')
                );
                /*$iss_atualizado_indice = round($diferenca_iss * $job->auditoria->fator_atualizacao,3);
                $iss_atualizado_multa = round($iss_atualizado_indice * (1 + $this->multaMunicipio/100),3);
                $iss_atualizado_juros = $this->calcJuros(
                    $iss_atualizado_multa,
                    $this->jurosMunicipio/100,
                    $this->diferencaMeses($job->auditoria->mes.'/'.$job->auditoria->ano,date('m/Y'))
                );*/
            }

            $data = [
                'iss_apurado' => $iss_apurado,
                'iss_recolhido' => $iss_recolhido,
                'diferenca_iss' => $diferenca_iss,
                'iss_atualizado_indice' => $iss_atualizado_indice,
                'iss_atualizado_multa' => $iss_atualizado_multa,
                'iss_atualizado_juros' => $iss_atualizado_juros,
                'status' => 1
            ];

            $job->update($data);
        }

    }

    protected function calcJuros($montante,$taxa,$meses){

        $meses = $meses + 1;

        Auditorias::find($this->auditoria->id)->update([
            'taxa_juros' => $meses
        ]);
        /*var_dump($montante);
        var_dump($taxa);
        var_dump($meses);*/
        $resultado = $montante * ($taxa * ($meses/100));
        /*var_dump($resultado);
        var_dump(round($montante - $resultado,3));*/
        return round($resultado,3);
        /*$resultado = $montante * pow((1 + $taxa), $meses);
        return round($resultado - $montante,3);*/
    }

    public static function diferencaMeses($dataInicial,$dataFinal){
        $toDate = Carbon::createFromFormat('m/Y',$dataFinal);
        $fromDate = Carbon::createFromFormat('m/Y',$dataInicial);
        return $fromDate->diffInMonths($toDate);
    }


    public static function getMesAno($filename){

        $filename = pathinfo($filename, PATHINFO_FILENAME);

        $isDms = Str::contains($filename, 'DMS');

        if (empty($isDms)){
            $quebra = explode('_',$filename);
            $data = end($quebra);

            $mes = substr($data,0,2);
            $ano = substr($data,2,6);

            return [$mes,$ano];
        }else{
            $mes = substr($filename,-6,2);
            $ano = substr($filename,-4);

            return [$mes,$ano];
        }


    }

    public static function formatNumber($valor){
        return number_format((float)$valor,2,',','.');
    }

}
