<?php

namespace App\Classes;

use App\Models\IndicesValores;
use Carbon\Carbon;
use DiDom\Document;
use DiDom\Query;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Indices
{
    public $indice;

    public $indiceId;

    public $indiceSlug;

    public $dataInicial;

    public $dataFinal;

    public $baseUri = "https://api.bcb.gov.br/dados/serie/bcdata.sgs.{indice}/dados?formato=json";

    protected static $uriCalculadoraBacen = "https://www3.bcb.gov.br/CALCIDADAO/publico/corrigirPorIndice.do?method=corrigirPorIndice";

    public $uri;

    public function __construct($indice,$dataInicial = null,$dataFinal = null){
        list($this->indiceId,$this->indice,$this->indiceSlug) = $this->setIndice($indice);
        $this->uri = $this->setUri();
    }

    public static function getFatorIndiceBacen($indiceSlug,$dataInicial,$dataFinal){

        $selIndice = '';

        if ($indiceSlug == 'ipca'){
            $selIndice = '00433IPCA';
            $request = Http::get("https://api.bcb.gov.br/dados/serie/bcdata.sgs.433/dados/ultimos/1?formato=json");
            $data = Carbon::createFromFormat('d/m/Y',$request->json()[0]['data'])->format('m/Y');
            if ($data != $dataFinal){
                $dataFinal = $data;
            }
        }

        $request = Http::asForm()->post(self::$uriCalculadoraBacen,[
            'aba' => '1',
            'selIndice' => $selIndice,
            'dataInicial' => $dataInicial,
            'dataFinal' => $dataFinal,
            'valorCorrecao' => '1000,00',
            'idIndice' => '',
            'nomeIndicePeriodo' => ''
        ]);

        if (!$request->ok()){
            throw new \Exception('Erro ao acessar calculadora do Bacen');
        }

        $body = $request->body();

        $document = new Document($body);

        $error = $document->has('.msgErro');

        if ($error){
            $msg = $document->find('.msgErro')[0]->text();
            throw new \Exception('Erro na calculadora: '.$msg);
        }

        $indice = $document->find('//table/tbody/tr/td/div[2]/table[1]/tbody/tr[6]/td[2]',Query::TYPE_XPATH)[0]->text();
        $indice = (float)str_replace(',','.',$indice);
        $indice = (float)number_format($indice,4);

        return $indice;

    }

    public function setUri(){
       return Str::replaceArray('{indice}', [$this->indice], $this->baseUri);
    }

    public function setIndice($indice){
        $find = \App\Models\Indices::where('slug',$indice)->first();
        if (!$find){
            throw new \Exception("Indice não encontrado!");
        }
        return [
            $find->id,
            $find->cod_bcb,
            $find->slug
        ];
    }

    public function getData(){
        $request = Http::get($this->uri);
        if (!$request->ok()){
            throw new \Exception("Erro ao acessar os dados do BCB!");
        }
        return $request->json();
    }

    public function updateData(){

        $dados = $this->getData();

        foreach ($dados as $item) {
            $data = Carbon::createFromFormat('d/m/Y',$item['data'])->format("Y-m-d H:i:s");
            $find = IndicesValores::where('id_indice',$this->indiceId)
                ->where('data',$data)
                ->first();
            if (!$find){
                IndicesValores::create([
                    'id_indice' => $this->indiceId,
                    'data' => $data,
                    'valor' => $item['valor']
                ]);
            }
        }
    }

}
