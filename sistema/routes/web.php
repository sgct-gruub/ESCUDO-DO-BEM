<?php

 use App\Middleware\AuthMiddleware;

/*
*  WEB ROUTES
*
*/


$app->get('/','HomeController:index')->add(new AuthMiddleware($container))->setName('home');
// $app->get('/financeiro/fluxo-de-caixa/json/categorias/{mes}/{ano}','FinanceiroController:getCategoriasDespesasJSON')->add(new AuthMiddleware($container))->setName('getCategoriasDespesasJSON');

// AUTH
$app->get('/register','AuthController:getRegister')->setName('register');
$app->post('/register','AuthController:postRegister');
$app->get('/login','AuthController:getSingin')->setName('login');
$app->get('/logout','AuthController:logout')->setName('logout');
$app->post('/login','AuthController:postSingin');

// CALENDÁRIO
$app->group('/calendario',function(){
	$this->get('','CalendarioController:listar')->setName('calendario');
})->add(new AuthMiddleware($container));

// UNIDADES
$app->group('/unidades',function(){
	// [GET]
	$this->get('','UnidadesController:listar')->setName('unidades');
	$this->get('/editar/{id}','UnidadesController:getEditar')->setName('editar_unidade');
	$this->get('/remover/{id}','UnidadesController:getRemover')->setName('remover_unidade');
	$this->get('/acessar/{id}','UnidadesController:getAcessar')->setName('acessar_unidade');
	$this->get('/sair/{id}','UnidadesController:getSair')->setName('sair_unidade');

	// [POST]
	$this->post('/cadastrar','UnidadesController:postCadastrar')->setName('post_cadastrar_unidade');
	$this->post('/editar/{id}','UnidadesController:postEditar')->setName('post_editar_unidade');

	// [QUARTOS GET]
	$this->get('/quartos/todos','UnidadesController:getQuartos')->setName('quartos');
	$this->get('/quartos','UnidadesController:getQuartosUnidade')->setName('quartos_unidade');
	$this->get('/remover/quarto/{id}','UnidadesController:getRemoverQuarto')->setName('remover_quarto_unidade');
	// [QUARTOS POST]
	$this->post('/cadastrar/quarto','UnidadesController:postCadastrarQuarto')->setName('post_cadastrar_quarto_unidade');
	$this->post('/editar/quarto/{id}','UnidadesController:postEditarQuarto')->setName('post_editar_quarto_unidade');

})->add(new AuthMiddleware($container));

// ACOLHIDOS
$app->group('/acolhidos',function(){

	$this->get('','AcolhidosController:listar')->setName('listar_acolhidos');
	$this->get('/status/{status}','AcolhidosController:listar')->setName('listar_acolhidos_status');
	$this->get('/cadastrar','AcolhidosController:getCadastrar')->setName('cadastrar_acolhido');
	$this->get('/editar/{id}','AcolhidosController:getEditar')->setName('editar_acolhido');
	$this->get('/remover/{id}','AcolhidosController:getRemover')->setName('remover_acolhido');
	$this->get('/remover/foto/{id}','AcolhidosController:getRemoverFoto')->setName('remover_foto_acolhido');
	$this->get('/remover/contato/{id}','AcolhidosController:getRemoverContato')->setName('remover_contato_acolhido');
	$this->get('/remover/arquivo/{id}','AcolhidosController:getRemoverArquivo')->setName('remover_arquivo_acolhido');

	$this->post('/cadastrar','AcolhidosController:postCadastrar')->setName('post_cadastrar_acolhido');
	$this->post('/cadastrar/contato/{id}','AcolhidosController:postCadastrarContato')->setName('post_cadastrar_contato_acolhido');
	$this->post('/cadastrar/arquivo/{id}','AcolhidosController:postCadastrarArquivo')->setName('post_cadastrar_arquivo_acolhido');
	$this->post('/editar/{id}','AcolhidosController:postEditar')->setName('post_editar_acolhido');
	$this->post('/editar/contato/{id}','AcolhidosController:postEditarContato')->setName('post_editar_contato_acolhido');
	$this->post('/editar/arquivo/{id}','AcolhidosController:postEditarArquivo')->setName('post_editar_arquivo_acolhido');

	// [ALTERAR UNIDADE]
	$this->post('/alterar_unidade/{id}','AcolhidosController:postAlterarUnidade')->setName('post_alterar_unidade_acolhido');

	// [CAIXA ACOLHIDOS]
	$this->get('/caixa/','CaixaAcolhidosController:listar')->setName('caixa');
	$this->get('/caixa/status/{status}','CaixaAcolhidosController:listar')->setName('caixa_status');
	$this->get('/caixa_acolhido/{id}','CaixaAcolhidosController:getCaixaAcolhido')->setName('caixa_acolhido');
	$this->post('/caixa_acolhido/{id}','CaixaAcolhidosController:postCadastrar')->setName('post_cadastrar_caixa_acolhido');
	$this->get('/remover/caixa_acolhido/{id}','CaixaAcolhidosController:getRemover')->setName('remover_caixa_acolhido');

	// [DOAÇÕES ACOLHIDOS]
	$this->get('/doacoes_acolhido/{id}','DoacoesAcolhidosController:listar')->setName('doacoes_acolhido');
})->add(new AuthMiddleware($container));

// ACOLHIMENTOS
$app->group('/acolhimentos',function(){

	$this->get('','AcolhimentosController:listar')->setName('acolhimentos');
	$this->get('/status/{status}','AcolhimentosController:listar')->setName('status_acolhimento');
	$this->get('/convenio/{convenio}','AcolhimentosController:listar')->setName('convenio_acolhimento');
	$this->get('/convenio/{convenio}/status/{status}','AcolhimentosController:listar')->setName('status_convenio_acolhimento');
	$this->get('/tipo/{tipo}','AcolhimentosController:listar')->setName('tipo_acolhimento');
	$this->get('/tipo/{tipo}/status/{status}','AcolhimentosController:listar')->setName('status_tipo_acolhimento');

	// $this->get('/cadastrar','AcolhimentosController:getCadastrar')->setName('cadastrar_acolhimento');
	$this->map(['GET', 'POST'], '/cadastrar','AcolhimentosController:getCadastrar')->setName('cadastrar_acolhimento');
	// $this->map(['GET', 'POST'], '/cadastrar/{tipo}','AcolhimentosController:getCadastrar')->setName('tipo_acolhimento');
	// $this->get('/editar/{id}','AcolhimentosController:getEditar')->setName('editar_acolhimento');
	$this->get('/remover/{id}','AcolhimentosController:getRemover')->setName('remover_acolhimento');

	$this->post('/','AcolhimentosController:postCadastrar')->setName('post_cadastrar_acolhimento');
	$this->post('/editar/{id}','AcolhimentosController:postEditar')->setName('post_editar_acolhimento');

	// [ALTERAR FASE]
	$this->post('/alterar_fase/{id}','AcolhimentosController:postAlterarFase')->setName('post_alterar_fase_acolhimento');

	// [ALTERAR UNIDADE]
	$this->post('/alterar_unidade/{id}','AcolhimentosController:postAlterarUnidade')->setName('post_alterar_unidade_acolhimento');

	// [ALTA]
	$this->post('/alta/{id}','AcolhimentosController:postCadastrarAlta')->setName('post_cadastrar_alta');
	$this->post('/alta/limpar/{id}','AcolhimentosController:postLimparAlta')->setName('post_limpar_alta');

	// [RESSOCIALIZAÇÃO]
	$this->post('/ressocializacao/{id}','AcolhimentosController:postRessocializacao')->setName('post_cadastrar_ressocializacao');
	$this->get('/remover/ressocializacao/{id}','AcolhimentosController:getRemoverRessocializacao')->setName('remover_ressocializacao');

	// [ARQUIVO]
	$this->post('/cadastrar/arquivo/{id}','AcolhimentosController:postCadastrarArquivo')->setName('post_cadastrar_arquivo_acolhimento');
	$this->post('/editar/arquivo/{id}','AcolhimentosController:postEditarArquivo')->setName('post_editar_arquivo_acolhimento');
	$this->get('/remover/arquivo/{id}','AcolhimentosController:getRemoverArquivo')->setName('remover_arquivo_acolhimento');

	// [CONTATO]
	$this->post('/cadastrar/contato/{id}','AcolhimentosController:postCadastrarContato')->setName('post_cadastrar_contato_acolhido_acolhimento');
	$this->post('/editar/contato/{id}','AcolhimentosController:postEditarContato')->setName('post_editar_contato_acolhido_acolhimento');
	$this->get('/remover/contato/{id}','AcolhimentosController:getRemoverContato')->setName('remover_contato_acolhido_acolhimento');

	// [TIMELINE]
	$this->get('/remover/timeline/{id}','AcolhimentosController:getRemoverTimeline')->setName('remover_timeline_acolhimento');

	// [PAS]
	$this->post('/abrir_pas/{id}','AcolhimentosController:abrirPas')->setName('abrir_pas');
	$this->post('/editar_pas/{id}','AcolhimentosController:postEditarPas')->setName('post_editar_pas');
	$this->post('/atividades_pas/{id}','AcolhimentosController:postAtividadesPas')->setName('post_atividades_pas');
	$this->post('/evolucao_pas/{id}','AcolhimentosController:postEvolucaoPas')->setName('post_evolucao_pas');
	$this->post('/metas_pas/{id}','AcolhimentosController:postMetasPas')->setName('post_metas_pas');
	$this->get('/remover_meta/{id}','AcolhimentosController:getRemoverMetaPas')->setName('remover_meta_pas');

	// [ESTUDO DE CASO]
	$this->post('/abrir_estudo_caso/{id}','AcolhimentosController:abrirEstudoCaso')->setName('abrir_estudo_caso');
	$this->post('/editar_estudo_caso/{id}','AcolhimentosController:editarEstudoCaso')->setName('editar_estudo_caso');
	$this->post('/evolucao_estudo_caso/{id}','AcolhimentosController:postEvolucaoEstudoCaso')->setName('post_evolucao_estudo_caso');
	$this->get('/remover_evolucao_estudo_caso/{id}','AcolhimentosController:getRemoverEvolucaoEstudoCaso')->setName('remover_evolucao_estudo_caso');
	$this->post('/metas_estudo_caso/{id}','AcolhimentosController:postMetasEstudoCaso')->setName('post_metas_estudo_caso');
	$this->get('/remover_meta_estudo_caso/{id}','AcolhimentosController:getRemoverMetaEstudoCaso')->setName('remover_meta_estudo_caso');
	$this->post('/relato_familiar_estudo_caso/{id}','AcolhimentosController:postRelatoFamiliarEstudoCaso')->setName('post_relato_familiar_estudo_caso');
})->add(new AuthMiddleware($container));

// ENCAMINHAMENTOS
$app->group('/encaminhamentos',function(){
	$this->get('','EncaminhamentosController:listar')->setName('encaminhamentos');
	$this->get('/cadastrar','EncaminhamentosController:getCadastrar')->setName('cadastrar_encaminhamento');
	$this->get('/editar/{id}','EncaminhamentosController:getEditar')->setName('editar_encaminhamento');
	$this->get('/remover/{id}','EncaminhamentosController:getRemover')->setName('remover_encaminhamento');
})->add(new AuthMiddleware($container));

// RESSOCIALIZAÇÕES
$app->group('/ressocializacoes',function(){
	$this->get('','RessocializacoesController:listar')->setName('ressocializacoes');
	$this->get('/cadastrar','RessocializacoesController:getCadastrar')->setName('cadastrar_ressocializacao');
	$this->get('/editar/{id}','RessocializacoesController:getEditar')->setName('editar_ressocializacao');
	$this->get('/remover/{id}','RessocializacoesController:getRemover')->setName('remover_ressocializacao');
})->add(new AuthMiddleware($container));

// OFÍCIOS
$app->group('/atividades-terapeuticas',function(){
	// [GET]
	$this->get('','OficiosController:listar')->setName('oficios');
	$this->get('/editar/{id}','OficiosController:getEditar')->setName('editar_oficio');
	$this->get('/remover/{id}','OficiosController:getRemover')->setName('remover_oficio');
	$this->get('/limpar/{id}','OficiosController:getLimpar')->setName('limpar_oficio');

	// [POST]
	$this->post('/cadastrar','OficiosController:postCadastrar')->setName('post_cadastrar_oficio');
	$this->post('/editar/{id}','OficiosController:postEditar')->setName('post_editar_oficio');
})->add(new AuthMiddleware($container));

// TÉCNICOS DE REFERÊNCIA
$app->group('/terapeutas',function(){
	// [GET]
	$this->get('','TecnicosReferenciaController:listar')->setName('tecnicos_referencia');
	$this->get('/editar/{id}','TecnicosReferenciaController:getEditar')->setName('editar_tecnico_referencia');
	$this->get('/remover/{id}','TecnicosReferenciaController:getRemover')->setName('remover_tecnico_referencia');

	// [POST]
	$this->post('/cadastrar','TecnicosReferenciaController:postCadastrar')->setName('post_cadastrar_tecnico_referencia');
	$this->post('/acolhidos/{id}','TecnicosReferenciaController:postAcolhidos')->setName('post_acolhidos_tecnico_referencia');
	$this->post('/editar/{id}','TecnicosReferenciaController:postEditar')->setName('post_editar_tecnico_referencia');
})->add(new AuthMiddleware($container));

// CONVÊNIOS
$app->group('/convenios',function(){
	// [GET]
	$this->get('','ConveniosController:listar')->setName('convenios');
	$this->get('/editar/{id}','ConveniosController:getEditar')->setName('editar_convenio');
	$this->get('/remover/{id}','ConveniosController:getRemover')->setName('remover_convenio');

	// [POST]
	$this->post('/cadastrar','ConveniosController:postCadastrar')->setName('post_cadastrar_convenio');
	$this->post('/editar/{id}','ConveniosController:postEditar')->setName('post_editar_convenio');
})->add(new AuthMiddleware($container));

// FINANCEIRO
$app->group('/financeiro',function(){

	// [Contas a Pagar]
	$this->group('/contas-a-pagar',function(){
		// [GET]
		$this->get('','FinanceiroController:listarContasPagar')->setName('contas_a_pagar');
		$this->get('/mes/{mes}/ano/{ano}','FinanceiroController:listarContasPagar')->setName('contas_a_pagar_mes_ano');
		$this->get('/cadastrar','FinanceiroController:getCadastrarContasPagar')->setName('cadastrar_conta_a_pagar');
		$this->get('/editar/{id}','FinanceiroController:getEditarContasPagar')->setName('editar_conta_a_pagar');
		$this->get('/remover/{id}','FinanceiroController:getRemoverContasPagar')->setName('remover_conta_a_pagar');
		$this->get('/remover-selecionadas','FinanceiroController:getRemoverContasPagarSelecionadas')->setName('remover_contas_a_pagar');


		// [POST]
		$this->post('/mes/{mes}/ano/{ano}','FinanceiroController:listarContasPagar');
		$this->post('/cadastrar','FinanceiroController:postCadastrarContasPagar')->setName('post_cadastrar_conta_a_pagar');
		$this->post('/editar/{id}','FinanceiroController:postEditarContasPagar')->setName('post_editar_conta_a_pagar');
		$this->post('/pagamento/{id}','FinanceiroController:postPagamentoContasPagar')->setName('post_pagamento_conta_a_pagar');
	});

	// [Contas a Receber]
	$this->group('/contas-a-receber',function(){
		// [GET]
		$this->get('','FinanceiroController:listarContasReceber')->setName('contas_a_receber');
		$this->get('/mes/{mes}/ano/{ano}','FinanceiroController:listarContasReceber')->setName('contas_a_receber_mes_ano');
		$this->get('/cadastrar','FinanceiroController:getCadastrarContasReceber')->setName('cadastrar_conta_a_receber');
		$this->get('/editar/{id}','FinanceiroController:getEditarContasReceber')->setName('editar_conta_a_receber');
		$this->get('/remover/{id}','FinanceiroController:getRemoverContasReceber')->setName('remover_conta_a_receber');
		$this->get('/remover-selecionadas','FinanceiroController:getRemoverContasReceberSelecionadas')->setName('remover_contas_a_receber');


		// [POST]
		$this->post('/cadastrar','FinanceiroController:postCadastrarContasReceber')->setName('post_cadastrar_conta_a_receber');
		$this->post('/editar/{id}','FinanceiroController:postEditarContasReceber')->setName('post_editar_conta_a_receber');
		$this->post('/recebimento/{id}','FinanceiroController:postRecebimentoContasReceber')->setName('post_recebimento_conta_a_receber');
	});

	// [Fluxo de Caixa]
	$this->group('/fluxo-de-caixa',function(){
		// [GET]
		$this->get('','FinanceiroController:fluxoDeCaixa')->setName('fluxo_de_caixa');
		$this->get('/{mes}/{ano}','FinanceiroController:fluxoDeCaixa')->setName('fluxo_de_caixa');
	});

	// [Mensalidades]
	$this->group('/mensalidades',function(){
		// [GET]
		$this->get('','FinanceiroController:listarMensalidades')->setName('mensalidades');
		$this->get('/{mes}','FinanceiroController:listarMensalidades')->setName('mensalidades_mes');

		// [POST]
		$this->post('/pagamento/{id}','FinanceiroController:postPagamentoMensalidade')->setName('post_pagamento_mensalidade');
		$this->post('/editar/{id}','FinanceiroController:postEditarMensalidade')->setName('post_editar_mensalidade');
	});

	// [Matriculas]
	$this->group('/matriculas',function(){
		// [GET]
		$this->get('','FinanceiroController:listarMatriculas')->setName('matriculas');

		// [POST]
		$this->post('/pagamento/{id}','FinanceiroController:postPagamentoMatricula')->setName('post_pagamento_matricula');
	});

	// [Categorias]
	$this->group('/categorias',function(){
		// [GET]
		$this->get('','FinanceiroController:listarCategorias')->setName('categorias');
		$this->get('/remover/{id}','FinanceiroController:getRemoverCategoria')->setName('remover_categoria');
		
		$this->get('/subcategoria/{id}','SubCategoriasController:listar')->setName('subcategoria');

		// [POST]
		$this->post('/cadastrar','FinanceiroController:postCadastrarCategoria')->setName('post_cadastrar_categoria');
		$this->post('/subcategoria/cadastrar','SubCategoriasController:postCadastrar')->setName('post_cadastrar_subcategoria');
		$this->post('/editar/{id}','FinanceiroController:postEditarCategoria')->setName('post_editar_categoria');
	});

	// [SubCategorias]
	$this->group('/subcategoria',function(){
		// [GET]
		$this->get('','SubCategoriasController:listar')->setName('subcategorias');
		$this->get('/remover/{id}','SubCategoriasController:getRemover')->setName('remover_subcategoria');

		// [POST]
		$this->post('/cadastrar','SubCategoriasController:postCadastrar')->setName('post_cadastrar_subcategoria');
		$this->post('/editar/{id}','SubCategoriasController:postEditar')->setName('post_editar_subcategoria');
	});

	// [Contas Bancárias]
	$this->group('/contas-bancarias',function(){
		// [GET]
		$this->get('','FinanceiroController:listarContasBancarias')->setName('contas_bancarias');
		$this->get('/remover/{id}','FinanceiroController:getRemoverContaBancaria')->setName('remover_conta');

		// [POST]
		$this->post('/cadastrar','FinanceiroController:postCadastrarContaBancaria')->setName('post_cadastrar_conta');
		$this->post('/editar/{id}','FinanceiroController:postEditarContaBancaria')->setName('post_editar_conta');
	});

	// CALENDÁRIO
	$this->group('/calendario',function(){
		$this->get('','FinanceiroController:listarCalendario')->setName('calendario_financeiro');
		$this->get('/listar','FinanceiroController:getListarEventos')->setName('listar_eventos');
		$this->get('/cadastrar','FinanceiroController:getCadastrarEvento')->setName('cadastrar_evento');
		$this->get('/editar/{id}','FinanceiroController:getEditarEvento')->setName('remover_evento');
		$this->get('/remover/{id}','FinanceiroController:getRemoverMovimentoCalendario')->setName('remover_evento');
	});
})->add(new AuthMiddleware($container));

// CANTINA
$app->group('/cantina',function(){
	// [Produtos]
	$this->get('/produtos','CantinaController:listarProdutos')->setName('produtos');
	$this->get('/produtos/remover/{id}','CantinaController:getRemoverProduto')->setName('remover_produto');

	$this->post('/produtos/cadastrar','CantinaController:postCadastrarProduto')->setName('post_cadastrar_produto');
	$this->post('/produtos/editar/{id}','CantinaController:postEditarProduto')->setName('post_editar_produto');

	// [Lançamentos]
	$this->get('/lancamentos','CantinaController:lancamentos')->setName('lancamentos');
	$this->get('/lancamentos/dia','CantinaController:lancamentosDia')->setName('lancamentos_dia');
	$this->get('/lancamentos/ultimos-7-dias','CantinaController:lancamentos7Dias')->setName('lancamentos_7dias');
	$this->get('/lancamentos/mes','CantinaController:lancamentosMes')->setName('lancamentos_mes');
	$this->get('/lancamentos/periodo','CantinaController:lancamentosPeriodo')->setName('lancamentos_periodo');
	$this->get('/lancamentos/acolhido/{id}','CantinaController:lancamentosAcolhido')->setName('lancamentos_acolhido');
	$this->get('/lancamentos/acolhido/{id}/dia','CantinaController:lancamentosAcolhidoDia')->setName('lancamentos_acolhido_dia');
	$this->get('/lancamentos/acolhido/{id}/ultimos-7-dias','CantinaController:lancamentosAcolhido7Dias')->setName('lancamentos_acolhido_7dias');
	$this->get('/lancamentos/acolhido/{id}/mes','CantinaController:lancamentosAcolhidoMes')->setName('lancamentos_acolhido_mes');
	$this->get('/lancamentos/acolhido/{id}/periodo','CantinaController:lancamentosAcolhidoPeriodo')->setName('lancamentos_acolhido_periodo');
	$this->get('/lancamentos/editar/{id}','CantinaController:getEditarLancamento')->setName('editar_lancamento');
	$this->get('/lancamentos/remover/{id}','CantinaController:getRemoverLancamento')->setName('remover_lancamento');
	$this->get('/lancamentos/remover-selecionados','CantinaController:getRemoverLancamentosSelecionados')->setName('remover_lancamentos');
	$this->post('/lancamentos/baixar/{id}','CantinaController:postBaixarLancamento')->setName('baixar_lancamento');
	$this->get('/lancamentos/baixar-selecionados','CantinaController:getBaixarLancamentosSelecionados')->setName('baixar_lancamentos');
	
	$this->get('/lancamento/editar/{id}','CantinaController:getEditarLancamento')->setName('editar_lancamento');
	$this->get('/lancamento/remover/item/{id}','CantinaController:getRemoverItemLancamento')->setName('remover_item_lancamento');

	$this->post('/lancamento/editar/{id}','CantinaController:postEditarLancamento')->setName('post_editar_lancamento');

	// [LIMITE CANTINA]
	$this->post('/limite/acolhido/{id}','CantinaController:postLimiteCantina')->setName('post_limite_cantina');

	$this->post('/lancamentos/cadastrar','CantinaController:postCadastrarLancamento')->setName('post_cadastrar_lancamento');
})->add(new AuthMiddleware($container));

// DESPENSA
// $app->group('/despensa',function(){
// 	// [Produtos]
// 	$this->get('/produtos','CantinaController:listarProdutos')->setName('produtos');
// 	$this->get('/produtos/cadastrar','CantinaController:getCadastrarProduto')->setName('editar_produto');
// 	$this->get('/produtos/editar/{id}','CantinaController:getEditarProduto')->setName('editar_produto');
// 	$this->get('/produtos/remover/{id}','CantinaController:getRemoverProduto')->setName('remover_produto');

// 	$this->post('/produtos/cadastrar','CantinaController:postCadastrarProduto')->setName('post_cadastrar_produto');
// 	$this->post('/produtos/editar/{id}','CantinaController:postEditarProduto')->setName('post_editar_produto');

// 	// [Entradas]
// 	$this->get('/entradas','CantinaController:listarEntradas')->setName('entradas');
// 	$this->get('/entradas/compra/cadastrar','CantinaController:getCadastrarCompra')->setName('cadastrar_compra');
// 	$this->get('/entradas/compra/editar/{id}','CantinaController:getEditarCompra')->setName('editar_compra');
// 	$this->get('/entradas/compra/remover/{id}','CantinaController:getRemoverCompra')->setName('remover_compra');

// 	$this->post('/entradas/compra/cadastrar','CantinaController:postCadastrarCompra')->setName('post_cadastrar_compra');

// 	// [Saidas]
// 	$this->get('/saidas','CantinaController:listarProdutosSaida')->setName('saidas');
// 	$this->post('/saidas','CantinaController:saidaEstoque')->setName('post_saida_estoque');

// 	// [Gerenciar]
// 	$this->get('/gerenciar-despensa','CantinaController:gerenciarDespensa')->setName('gerenciar');
// 	$this->get('/gerenciar-despensa/remover/{id}','CantinaController:getRemoverSaida')->setName('remover_saida');

// 	// [Doações]
// 	$this->get('/entradas/doacoes/cadastrar','CantinaController:getCadastrarDoacao')->setName('cadastrar_doacao');
// 	$this->get('/entradas/doacoes/editar/{id}','CantinaController:getEditarDoacao')->setName('editar_doacao');
// 	$this->get('/entradas/doacoes/remover/{id}','CantinaController:getRemoverDoacao')->setName('remover_doacao');

// 	$this->post('/entradas/doacoes/cadastrar','CantinaController:postCadastrarDoacao')->setName('post_cadastrar_doacao');
// })->add(new AuthMiddleware($container));

// FORNECEDORES
$app->group('/fornecedores',function(){
	// [GET]
	$this->get('','FornecedoresController:listar')->setName('fornecedores');
	$this->get('/cadastrar','FornecedoresController:getCadastrar')->setName('cadastrar_fornecedor');
	$this->get('/editar/{id}','FornecedoresController:getEditar')->setName('editar_fornecedor');
	$this->get('/remover/{id}','FornecedoresController:getRemover')->setName('remover_fornecedor');

	// [POST]
	$this->post('/cadastrar','FornecedoresController:postCadastrar')->setName('post_cadastrar_fornecedor');
	$this->post('/editar/{id}','FornecedoresController:postEditar')->setName('post_editar_fornecedor');
})->add(new AuthMiddleware($container));

// DOAÇÕES
$app->group('/doacoes',function(){
	// REDES
	// [GET]
	$this->get('/redes','RedesController:listar')->setName('redes');
	$this->get('/redes/cadastrar','RedesController:getCadastrar')->setName('cadastrar_rede');
	$this->get('/redes/editar/{id}','RedesController:getEditar')->setName('editar_rede');
	$this->get('/redes/remover/{id}','RedesController:getRemover')->setName('remover_rede');

	// [POST]
	$this->post('/redes/cadastrar','RedesController:postCadastrar')->setName('post_cadastrar_rede');
	$this->post('/redes/editar/{id}','RedesController:postEditar')->setName('post_editar_rede');

	// DOADORES
	// [GET]
	$this->get('/doadores','DoadoresController:listar')->setName('doadores');
	$this->get('/doadores/cadastrar','DoadoresController:getCadastrar')->setName('cadastrar_doador');
	$this->get('/doadores/editar/{id}','DoadoresController:getEditar')->setName('editar_doador');
	$this->get('/doadores/remover/{id}','DoadoresController:getRemover')->setName('remover_doador');

	// [POST]
	$this->post('/doadores/cadastrar','DoadoresController:postCadastrar')->setName('post_cadastrar_doador');
	$this->post('/doadores/editar/{id}','DoadoresController:postEditar')->setName('post_editar_doador');

	// PLANOS
	// [GET]
	$this->get('/planos','JunoController:planos')->setName('planos');
	// [POST]
	$this->post('/planos/cadastrar','JunoController:createPlan')->setName('post_cadastrar_plano');

	// PLANOS
	// [GET]
	$this->get('/assinaturas','JunoController:assinaturas')->setName('assinaturas');
	// [POST]
	// $this->post('/assinaturas/cadastrar','JunoController:createSubscription')->setName('post_cadastrar_assinatura');
})->add(new AuthMiddleware($container));

// CONTRATOS
$app->group('/contratos',function(){
	// [GET]
	$this->get('','ContratosController:listar')->setName('contratos');
	$this->get('/cadastrar','ContratosController:getCadastrar')->setName('cadastrar_contrato');
	$this->get('/editar/{id}','ContratosController:getEditar')->setName('editar_contrato');
	$this->get('/remover/{id}','ContratosController:getRemover')->setName('remover_contrato');

	// [POST]
	$this->post('/cadastrar','ContratosController:postCadastrar')->setName('post_cadastrar_contrato');
	$this->post('/editar/{id}','ContratosController:postEditar')->setName('post_editar_contrato');
})->add(new AuthMiddleware($container));

// USUÁRIOS
$app->group('/usuarios',function(){
	// [GET]
	$this->get('','UsersController:getRead')->setName('usuarios');
	$this->get('/cadastrar','UsersController:getCreate')->setName('create_usuario');
	$this->get('/editar/{id}','UsersController:getUpdate')->setName('update_usuario');
	$this->get('/remover/{id}','UsersController:getDelete')->setName('delete_usuario');

	// [POST]
	$this->post('/cadastrar','UsersController:postCreate')->setName('post_create_usuario');
	$this->post('/editar/{id}','UsersController:postUpdate')->setName('post_update_usuario');
})->add(new AuthMiddleware($container));

// FUNCIONÁRIOS
$app->group('/funcionarios',function(){
	// [GET]
	$this->get('','FuncionariosController:listar')->setName('funcionarios');
	$this->get('/cadastrar','FuncionariosController:getCadastrar')->setName('cadastrar_funcionario');
	$this->get('/editar/{id}','FuncionariosController:getEditar')->setName('editar_funcionario');
	$this->get('/remover/{id}','FuncionariosController:getRemover')->setName('remover_funcionario');
	$this->get('/remover/arquivo/{id}','FuncionariosController:getRemoverArquivo')->setName('remover_arquivo_funcionario');
	$this->get('/remover/advertencia/{id}','FuncionariosController:getRemoverAdvertencia')->setName('remover_advertencia_funcionario');
	$this->get('/remover/timeline/{id}','FuncionariosController:getRemoverTimeline')->setName('remover_timeline_funcionario');

	// [POST]
	$this->post('/cadastrar','FuncionariosController:postCadastrar')->setName('post_cadastrar_funcionario');
	$this->post('/cadastrar/arquivo/{id}','FuncionariosController:postCadastrarArquivo')->setName('post_cadastrar_arquivo_funcionario');
	$this->post('/cadastrar/advertencia/{id}','FuncionariosController:postCadastrarAdvertencia')->setName('post_cadastrar_advertencia_funcionario');
	$this->post('/editar/{id}','FuncionariosController:postEditar')->setName('post_editar_funcionario');
})->add(new AuthMiddleware($container));

// CONFIGURAÇÕES
$app->group('/config',function(){

	$this->get('/instituicao','ConfigController:index')->setName('config');
	$this->post('/instituicao','ConfigController:salvar')->setName('salvar_config');

	/*
		- Configs
		-- Cronograma de atividades [GET]
	*/
	$this->get('/cronograma-de-atividades','ConfigController:cronograma_atividades')->setName('config_cronograma_atividades');
	$this->get('/cronograma-de-atividades/remover/{id}','ConfigController:remover_atividade')->setName('remover_atividade');
	$this->get('/cronograma-de-atividades/remover-grupo/{id}','ConfigController:remover_grupo_atividade')->setName('remover_grupo_atividade');

	/*
		- Configs
		-- Cronograma de atividades [POST]
	*/
	$this->post('/cronograma-de-atividades/cadastrar','ConfigController:criar_atividade')->setName('post_criar_atividade');
	$this->post('/cronograma-de-atividades/editar/{id}','ConfigController:editar_atividade')->setName('post_editar_atividade');
	$this->post('/cronograma-de-atividades/editar-grupo/{id}','ConfigController:editar_grupo_atividade')->setName('post_editar_grupo_atividade');

	/*
		- Configs
		-- Usuários [GET]
	*/
	$this->get('/grupos-de-usuarios','ConfigController:usuarios')->setName('config_usuarios');
	$this->get('/grupos-de-usuarios/remover/{id}','ConfigController:remover_grupo')->setName('remover_grupo');

	/*
		- Configs
		-- Usuários [POST]
	*/
	$this->post('/grupos-de-usuarios/cadastrar','ConfigController:criar_grupo')->setName('post_criar_grupo');
	$this->post('/grupos-de-usuarios/editar/{id}','ConfigController:editar_grupo')->setName('post_editar_grupo');
})->add(new AuthMiddleware($container));

// RELATÓRIOS
$app->group('/relatorios',function(){	
	// ALTAS
	$this->get('/altas-por-periodo','RelatoriosController:altas')->setName('altas');
	
	// ACOLHIMENTOS
	$this->get('/acolhimentos-por-periodo','RelatoriosController:acolhimentos_por_periodo')->setName('acolhimentos_por_periodo');
	
	// ACOLHIMENTOS P/ REGIÃO
	$this->get('/acolhidos-por-regiao','RelatoriosController:acolhimentos_por_regiao')->setName('acolhimentos_por_regiao');
})->add(new AuthMiddleware($container));

// LISTAS
$app->group('/listas',function(){
	// LISTA DINÂMICA
	$this->get('/lista-dinamica','RelatoriosController:lista_dinamica')->setName('lista_dinamica');
	
	// LISTA DE CHAMADA
	$this->get('/lista-de-chamada','RelatoriosController:lista_chamada')->setName('lista_chamada');
	
	// LISTA DE MEDICAÇÃO DIÁRIA
	$this->get('/lista-de-medicacao-diaria','RelatoriosController:lista_medicacao_diaria')->setName('lista_medicacao_diaria');
})->add(new AuthMiddleware($container));

// GRÁFICOS
$app->get('/graficos','GraficosController:index')->add(new AuthMiddleware($container))->setName('graficos');

// MENSAGENS
$app->group('/mensagens',function(){
	// [GET]
	$this->get('','MensagensController:listar')->setName('mensagens');
	$this->get('/enviadas','MensagensController:listarEnviadas')->setName('mensagens_enviadas');
	$this->get('/ler/{id}','MensagensController:getLer')->setName('ler_mensagem');
	$this->get('/remover/{id}','MensagensController:getRemover')->setName('remover_convenio');

	// [POST]
	$this->post('/cadastrar','MensagensController:postCadastrar')->setName('post_nova_mensagem');
	$this->post('/editar/{id}','MensagensController:postEditar')->setName('post_editar_convenio');
})->add(new AuthMiddleware($container));