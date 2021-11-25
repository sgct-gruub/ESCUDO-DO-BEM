<?php

session_start();

require 'vendor/autoload.php';

$app = new \Slim\App([
    'settings' => [
        // Slim Settings
        'determineRouteBeforeAppMiddleware' => true,
        'displayErrorDetails' => true,
        'debug' => true,
        'db' => [
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => 'escudodobem',
            'username' => 'root',
            'password' => 'bb744e9e47',
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
        ]
    ],
]);

$container = $app->getContainer();

//elquent config
$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container['settings']['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$container['db'] = function ($container) {
    return $capsule;
};

// twig config
$container['view'] = function ($c) {
    $view = new \Slim\Views\Twig('views', [
        'debug' => true,
        'cache' => false
    ]);
    $basePath = rtrim(str_ireplace('index.php', '', $c['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($c['router'], $basePath));
    // $view->addExtension(new Twig_Extension_Debug());
    
    $view->getEnvironment()->addGlobal('session', $_SESSION);
    $view->getEnvironment()->addGlobal('get', $_GET);
    $view->getEnvironment()->addGlobal('post', $_POST);
    return $view;
};

//Override the default Not Found Handler
// $container['notFoundHandler'] = function ($container) {
//     return function ($request, $response) use ($container) {
//         return $container['view']
//           ->render($response->withStatus(400), 'error/400.html');
//     };
// };

// $container['notFoundHandler'] = function ($container) {
//     return function ($request, $response) use ($container) {
//         return $container['view']
//           ->render($response->withStatus(403), 'error/403.html');
//     };
// };

// //Override the default Not Found Handler
// $container['notFoundHandler'] = function ($container) {
//     return function ($request, $response) use ($container) {
//         return $container['view']
//           ->render($response->withStatus(404), 'error/404.html');
//     };
// };


// // Custom error handler
// $container['errorHandler'] = function ($container) {
//     return function ($request, $response, $exception) use ($container) {
//         return $container['view']
//           ->render($response->withStatus(500), 'error/500.html');
//     };
// };


// // Custom error handler
// $container['errorHandler'] = function ($container) {
//     return function ($request, $response, $exception) use ($container) {
//         return $container['view']
//           ->render($response->withStatus(503), 'error/503.html');
//     };
// };



// Authentification
$container['Auth'] = function ($container) {
    return new \App\Auth\Auth;
};

//csrf
$container['csrf'] = function ($container) {
    return new \Slim\Csrf\Guard;
};

// Register provider
$container['flash'] = function () {
    return new \Slim\Flash\Messages();
};

// Middleware
$app->add(new \App\Middleware\ValidationMiddleware($container));
$app->add(new \App\Middleware\CorrectRequestMiddleware($container));
$app->add(new \App\Middleware\CsrfMiddleware($container));
$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});
$app->add($container->csrf);


// controllers
$container['Validator']= function($container){
  return new \App\Validation\Validator($container);
};

$container['AuthController']= function($container){
  return new \App\Controllers\Auth\AuthController($container);
};

/*
DashBoard
**********
*/
$container['HomeController']= function($container){
  return new \App\Controllers\HomeController($container);
};

/*
Calendário
**********
*/
$container['CalendarioController']= function($container){
  return new \App\Controllers\CalendarioController($container);
};

/*
Financeiro
**********
*/
$container['FinanceiroController']= function($container){
  return new \App\Controllers\FinanceiroController($container);
};

/*
Financeiro - SubCategorias
**********
*/
$container['SubCategoriasController']= function($container){
  return new \App\Controllers\Financeiro\SubCategoriasController($container);
};

/*
Unidades
**********
*/
$container['UnidadesController']= function($container){
  return new \App\Controllers\UnidadesController($container);
};

/*
Acolhidos
**********
*/
$container['AcolhidosController']= function($container){
  return new \App\Controllers\AcolhidosController($container);
};

/*
CaixaAcolhidos
**********
*/
$container['CaixaAcolhidosController']= function($container){
  return new \App\Controllers\CaixaAcolhidosController($container);
};

/*
Acolhimentos
**********
*/
$container['AcolhimentosController']= function($container){
  return new \App\Controllers\AcolhimentosController($container);
};

/*
Encaminhamentos
**********
*/
$container['EncaminhamentosController']= function($container){
  return new \App\Controllers\EncaminhamentosController($container);
};

/*
Ressocializações
**********
*/
$container['RessocializacoesController']= function($container){
  return new \App\Controllers\RessocializacoesController($container);
};

/*
Ofícios
**********
*/
$container['OficiosController']= function($container){
  return new \App\Controllers\OficiosController($container);
};

/*
Convênios
**********
*/
$container['ConveniosController']= function($container){
  return new \App\Controllers\ConveniosController($container);
};

/*
Despensa
**********
*/
$container['CantinaController']= function($container){
  return new \App\Controllers\CantinaController($container);
};

/*
Doações Acolhidos
**********
*/
$container['DoacoesAcolhidosController']= function($container){
  return new \App\Controllers\DoacoesAcolhidosController($container);
};

/*
Fornecedores
**********
*/
$container['FornecedoresController']= function($container){
  return new \App\Controllers\FornecedoresController($container);
};

/*
Redes
**********
*/
$container['RedesController']= function($container){
  return new \App\Controllers\Doacoes\RedesController($container);
};

/*
Doadores
**********
*/
$container['DoadoresController']= function($container){
  return new \App\Controllers\DoadoresController($container);
};

/*
Contratos
**********
*/
$container['ContratosController']= function($container){
  return new \App\Controllers\ContratosController($container);
};

/*
Configurações
**********
*/
$container['ConfigController']= function($container){
  return new \App\Controllers\ConfigController($container);
};

/*
Usuários
**********
*/
$container['UsersController']= function($container){
  return new \App\Controllers\UsersController($container);
};

/*
Funcionários
**********
*/
$container['FuncionariosController']= function($container){
  return new \App\Controllers\FuncionariosController($container);
};

/*
Relatórios
**********
*/
$container['RelatoriosController']= function($container){
  return new \App\Controllers\RelatoriosController($container);
};

/*
Técnicos de Referência
**********
*/
$container['TecnicosReferenciaController']= function($container){
  return new \App\Controllers\TecnicosReferenciaController($container);
};

/*
Gráficos
**********
*/
$container['GraficosController']= function($container){
  return new \App\Controllers\GraficosController($container);
};

/*
Juno
**********
*/
$container['JunoController']= function($container){
  return new \App\Controllers\JunoController($container);
};

/*
Mensagens
**********
*/
$container['MensagensController']= function($container){
  return new \App\Controllers\MensagensController($container);
};

/* SANDBOX */
define ('PRIVATE_TOKEN_SANDBOX', '4507D753F7BE608FFB5A4FCE5F0C3030CC2752F1BB86817085FE6480FD47CEA7');
define ('CLIENT_ID_SANDBOX', 'TM4geyL7yG0rK598');
define ('CLIENT_SECRET_SANDBOX', 'cTslgo?W~L?DuGPUUMP*hgv1(sk_Q50B');

/* PRODUÇÃO */
define ('PRIVATE_TOKEN_PRODUCAO', 'D1971F3548962339BA2FE58A7341946F9AD978694F80065AF81BB9A161606796');
define ('CLIENT_ID_PRODUCAO', '8PU5wmkgc4TzuoKO');
define ('CLIENT_SECRET_PRODUCAO', 'aok.%;(izmS8ggW5^64OJ4!EJ^V_<&He');