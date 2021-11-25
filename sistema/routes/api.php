<?php

/*
* API ROUTES
*
*/

$app->get('/buscar_quarto/{id}','UnidadesController:buscar_quarto')->setName('buscar_quarto');

$app->get('/acolhimentos/editar/{id}','AcolhimentosController:novo')->setName('editar_acolhimento');
// $app->get('/acolhimentos/novo/{id}','AcolhimentosController:novo')->setName('acolhimento_novo');
// $app->map(['GET', 'POST'], '/acolhimentos/novo/cadastrar','AcolhimentosController:cadastrar')->setName('cadastrar_acolhimento_novo');
$app->post('/acolhimentos/cadastrar/novo','AcolhimentosController:add_acolhimento')->setName('post_cadastrar_acolhimento_novo');
$app->post('/acolhimentos/novo/editar/{id}','AcolhimentosController:editar_acolhimento')->setName('post_editar_acolhimento_novo');

// FICHAS DE EVOLUÇÃO
$app->get('/acolhimentos/ficha-evolucao/{id}','AcolhimentosController:ficha_evolucao')->setName('ficha_evolucao');
$app->post('/acolhimentos/ficha-evolucao/add','AcolhimentosController:add_ficha_evolucao')->setName('add_ficha_evolucao');
$app->post('/acolhimentos/ficha-evolucao/editar/{id}','AcolhimentosController:editar_ficha_evolucao')->setName('editar_ficha_evolucao');
$app->get('/acolhimentos/ficha-evolucao/remover/{id}','AcolhimentosController:remover_ficha_evolucao')->setName('remover_ficha_evolucao');

// PARECER PROFISSIONAL
$app->get('/acolhimentos/parecer-profissional/{id}','AcolhimentosController:parecer_profissional')->setName('parecer_profissional');
$app->post('/acolhimentos/parecer-profissional/add','AcolhimentosController:add_parecer_profissional')->setName('add_parecer_profissional');
$app->post('/acolhimentos/parecer-profissional/editar/{id}','AcolhimentosController:editar_parecer_profissional')->setName('editar_parecer_profissional');
$app->get('/acolhimentos/parecer-profissional/remover/{id}','AcolhimentosController:remover_parecer_profissional')->setName('remover_parecer_profissional');

// TIMELINE
$app->get('/acolhimentos/linha-do-tempo/{id}','AcolhimentosController:timeline')->setName('timeline');

// ALTERAR UNIDADE
$app->get('/acolhimentos/alterar-unidade/{id}','AcolhimentosController:alterar_unidade')->setName('alterar_unidade');

// DESLIGAMENTO
$app->get('/acolhimentos/desligamento/{id}','AcolhimentosController:desligamento')->setName('desligamento');

// FICHA DE ENTREVISTA
$app->get('/acolhimentos/ficha-entrevista/{id}','AcolhimentosController:ficha_entrevista')->setName('ficha_entrevista');
$app->post('/acolhimentos/ficha-entrevista/add','AcolhimentosController:add_ficha_entrevista')->setName('add_ficha_entrevista');

$app->get('/doacoes/cobrancas/criar','JunoController:createCharge');
$app->get('/doacoes/cobrancas/visualizar/{id}','JunoController:getCharge');
$app->get('/doacoes/cobrancas','JunoController:getCharges')->setName('cobrancas');
$app->get('/juno/form','JunoController:form')->setName('juno');
$app->get('/doacoes/assinaturas/cadastrar','JunoController:createSubscription')->setName('post_cadastrar_assinatura');