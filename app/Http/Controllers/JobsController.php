<?php

namespace App\Http\Controllers;

use App\Models\Auditorias;
use App\Models\Jobs;

class JobsController extends Controller
{
    public function index()
    {

        $data = [];
        $resultado = [];

        $base_calculo = 0;
        $iss_apurado = 0;
        $iss_recolhido = 0;
        $diferenca_iss = 0;
        $iss_atualizado_indice = 0;
        $iss_atualizado_juros = 0;
        $iss_atualizado_multa = 0;
        $valor_devido = 0;

        $auditorias = Auditorias::where('ano',2023)
            ->where('status',1)
            ->get();

           

        foreach ($auditorias as $key => $auditoria) {

            $jobs = \App\Models\Jobs::where('id_auditoria',$auditoria->id)
                ->where('status',1)
                ->where('base_calculo','>',0)
                ->where('diferenca_iss','>=',0.01)
                ->orderBy('base_calculo')
                ->get();


            $data[$key]['rows'] = $jobs;
            $data[$key]['mes'] = $auditoria->mes;
            $data[$key]['ano'] = $auditoria->ano;
            $data[$key]['base_calculo'] = 0;
            $data[$key]['iss_apurado'] = 0;
            $data[$key]['iss_recolhido'] = 0;
            $data[$key]['diferenca_iss'] = 0;
            $data[$key]['iss_atualizado_indice'] = 0;
            $data[$key]['iss_atualizado_juros'] = 0;
            $data[$key]['iss_atualizado_multa'] = 0;
            $data[$key]['valor_devido'] = 0;

            $resultado[$key]['mes'] = $auditoria->mes;
            $resultado[$key]['ano'] = $auditoria->ano;
            $resultado[$key]['base_calculo'] = 0;
            $resultado[$key]['iss_apurado'] = 0;
            $resultado[$key]['iss_recolhido'] = 0;
            $resultado[$key]['diferenca_iss'] = 0;
            $resultado[$key]['iss_atualizado_indice'] = 0;
            $resultado[$key]['iss_atualizado_juros'] = 0;
            $resultado[$key]['iss_atualizado_multa'] = 0;
            $resultado[$key]['valor_devido'] = 0;

            foreach ($jobs as $row) {
                $data[$key]['base_calculo'] = $data[$key]['base_calculo'] + $base_calculo + (float)$row->base_calculo;
                $data[$key]['iss_apurado'] = $data[$key]['iss_apurado'] + $iss_apurado + (float)$row->iss_apurado;
                $data[$key]['iss_recolhido'] = $data[$key]['iss_recolhido'] + $iss_recolhido + (float)$row->iss_recolhido;
                $data[$key]['diferenca_iss'] = $data[$key]['diferenca_iss'] + $diferenca_iss + (float)$row->diferenca_iss;
                $data[$key]['iss_atualizado_indice'] = $data[$key]['iss_atualizado_indice'] + (float)$iss_atualizado_indice + ((float)$row->diferenca_iss + (float)$row->iss_atualizado_indice);
                $data[$key]['iss_atualizado_juros'] = $data[$key]['iss_atualizado_juros'] + $iss_atualizado_juros + (float)$row->iss_atualizado_juros;
                $data[$key]['iss_atualizado_multa'] = $data[$key]['iss_atualizado_multa'] + $iss_atualizado_multa + (float)$row->iss_atualizado_multa;
                $data[$key]['valor_devido'] = $data[$key]['valor_devido'] + $valor_devido + ($row->diferenca_iss + $row->iss_atualizado_indice + $row->iss_atualizado_juros + $row->iss_atualizado_multa);

                $resultado[$key]['base_calculo'] = $resultado[$key]['base_calculo'] + $base_calculo + (float)$row->base_calculo;
                $resultado[$key]['iss_apurado'] = $resultado[$key]['iss_apurado'] + $iss_apurado + (float)$row->iss_apurado;
                $resultado[$key]['iss_recolhido'] = $resultado[$key]['iss_recolhido'] + $iss_recolhido + (float)$row->iss_recolhido;
                $resultado[$key]['diferenca_iss'] = $resultado[$key]['diferenca_iss'] + $diferenca_iss + (float)$row->diferenca_iss;
                $resultado[$key]['iss_atualizado_indice'] = $resultado[$key]['iss_atualizado_indice'] + (float)$iss_atualizado_indice + ((float)$row->diferenca_iss + (float)$row->iss_atualizado_indice);
                $resultado[$key]['iss_atualizado_juros'] = $resultado[$key]['iss_atualizado_juros'] + $iss_atualizado_juros + (float)$row->iss_atualizado_juros;
                $resultado[$key]['iss_atualizado_multa'] = $resultado[$key]['iss_atualizado_multa'] + $iss_atualizado_multa + (float)$row->iss_atualizado_multa;
                $resultado[$key]['valor_devido'] = $resultado[$key]['valor_devido'] + $valor_devido + ($row->diferenca_iss + $row->iss_atualizado_indice + $row->iss_atualizado_juros + $row->iss_atualizado_multa);
            }

        }

        //print_r($data);exit;

        return view('joaquim',[
            'data' => $data,
            'resultado' => $resultado
        ]);

    }
}
