<?php

namespace App\Http\Controllers;

use App\Classes\Processamentos;
use App\Models\Auditorias;

class ProcessamentoAuditoriasController extends Controller
{
    public function index()
    {

        $processamento = new Processamentos();
        //$auditorias = Auditorias::where('status',0)->get();
        $auditorias = Auditorias::where('status',0)->limit(1)->get();
        foreach ($auditorias as $auditoria){
            $processamento->getAuditoria($auditoria->id);
            $processamento->setDadosMunicipio();
            $processamento->getListcosifs();
            //$processamento->newJob($auditoria->mes,$auditoria->ano);
            $processamento->proccessJob($auditoria->id);
        }
        //$processamento->createFromRange('2018-07-01','2023-08-01',1);

        //$processamento->processEstban();
        //$processamento->newJob($processamento->mes,$processamento->ano);
        //$processamento->proccessJob(9);

    }
}
