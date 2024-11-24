<?php

use App\Classes\Indices;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('processamentobam',"\App\Http\Controllers\ProcessamentoBamController@index");
Route::get('processamentodms',"\App\Http\Controllers\ProcessamentoDmsController@index");
Route::get('processamentoauditorias',"\App\Http\Controllers\ProcessamentoAuditoriasController@index");
Route::get('indices',"\App\Http\Controllers\IndicesController@index");
Route::get('jobs',"\App\Http\Controllers\JobsController@index");

Route::get('teste',function (){

    /*$dmsProcessados = \App\Models\DmsProcessador::get();
    foreach ($dmsProcessados as $item) {
        $cosif = $item->cosif;
        $rubrica = $item->rubrica;

        $cosifExists = \App\Models\CosifsContas::where('cosif',$cosif)
            ->where('conta',$rubrica)
            ->exists();
        if (!$cosifExists){
            \App\Models\CosifsContas::create([
                'cosif' => $cosif,
                'conta' => $rubrica
            ]);
        }

        $cosifExists = \App\Models\Cosifs::where('cosif',$cosif)
            ->exists();
        if (!$cosifExists){
            \App\Models\Cosifs::create([
                'cosif' => $cosif,
            ]);
        }

    }

    exit();*/


        /*$data['rows'] = \App\Models\Jobs::where('id_auditoria',11)
            ->where('status',1)
            ->where('base_calculo','>',0)
            ->where('diferenca_iss','>=',0.01)
            ->orderBy('base_calculo')
            ->get();


        $data['mes'] = $data['rows'][0]->auditoria->mes;
        $data['ano'] = $data['rows'][0]->auditoria->ano;

        $data['aliquota'] = $data['rows'][0]->auditoria->agencia->cidade->aliquota/100;

        return view('result',['data' => $data]);*/

        $auditorias = \App\Models\Auditorias::whereNull('fator_atualizacao')->get();

        foreach ($auditorias as $auditoria) {

            $data = $auditoria->mes."/".$auditoria->ano;

            $indice = Indices::getFatorIndiceBacen('ipca',$data,'06/2024');

            $auditoria->update([
                'fator_atualizacao' => $indice
            ]);

        }


});
