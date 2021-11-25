<?php

namespace App\Controllers;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig as View;
use \GuzzleHttp\Exception\ClientException as GuzzleClientException;

use App\Models\Users;
use App\Models\Doadores;
use App\Models\Doacoes\Charges;
use App\Models\Doacoes\Redes;

class JunoController extends Controller
{

    public function form($request, $response, $args)
    {
        $mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'juno/form.html', [
            'Titulo_Pagina' => '',
        ]);
    }

    public function createCharge($request, $response, $args)
    {
        $token = array(
            'PRIVATE_TOKEN' => PRIVATE_TOKEN_PRODUCAO,
            'CLIENT_ID' => CLIENT_ID_PRODUCAO,
            'CLIENT_SECRET' => CLIENT_SECRET_PRODUCAO
        );

        // if($request->getQueryParams()['amount'] != ''){
        //     $amount = $request->getQueryParams()['amount'];
        // } else {
        //     $res = ['status' => 'erro', 'msg' => 'Selecione o valor da doação'];
        //     print_r($res);
        // }

        if($request->getQueryParams()['amount'] != ''){
            $amount = $request->getQueryParams()['amount'];
        }

        if($request->getQueryParams()['other_amount'] != ''){
            $amount = $request->getQueryParams()['other_amount'];
        }

        if($request->getQueryParams()['payment_method'] == 'BOLETO'){
            try {
                $chargeService = new Juno\Charge($token);
                $charge = $chargeService->createCharge([
                    'charge' => [
                        // // 'pixKey' => '2333f697-e4b3-4cfd-add1-37bdf6427a2d',
                        // 'pixKey' => '8a66e691-a26f-4e95-8f82-29b6d635002e',
                        // 'pixIncludeImage' => true,
                        'description' => 'Doação',
                        'amount' => $amount,
                        'paymentTypes' => [$request->getQueryParams()['payment_method']],
                    ],
                    'billing' => [
                        'name' => $request->getQueryParams()['name'],
                        'document' => $request->getQueryParams()['document'],
                        'email' => $request->getQueryParams()['email'],
                        'birthDate' => '',
                        'notify' => true
                    ]
                ]);
            } catch (GuzzleClientException $e) {
                print_r($e->getResponse()->getBody()->getContents());
            }

            if($charge->_embedded->charges[0]){
                $doador = Doadores::where('cpf', $request->getQueryParams()['document'])->count();
                if($doador < 1){
                    $item = Doadores::create([
                        'nome' => $request->getQueryParams()['name'],
                        'email' => $request->getQueryParams()['email'],
                        'cpf' => $request->getQueryParams()['document'],
                        'telefone' => $request->getQueryParams()['phone'],
                        'rede' => $request->getQueryParams()['rede'],
                    ]);
                } else {
                    $item = Doadores::where('cpf', $request->getQueryParams()['document'])->first();
                }  

                if($item){
                    $cobranca = Charges::create([
                        'doador' => $item->id,
                        'amount' => $amount,
                        'paymentTypes' => $request->getQueryParams()['payment_method'],
                        'chargeId' => $charge->_embedded->charges[0]->id,
                        'chargeStatus' => $charge->_embedded->charges[0]->status
                    ]);
                } else{
                    return false;
                }
                echo $charge->_embedded->charges[0]->link;
            } else {
                echo 'Não foi possível gerar a cobrança!';
            }
        }

        if($request->getQueryParams()['payment_method'] == 'CREDIT_CARD'){
            $doador = Doadores::where('cpf', $request->getQueryParams()['document'])->count();
            if($doador < 1){
                $item = Doadores::create([
                    'nome' => $request->getQueryParams()['name'],
                    'email' => $request->getQueryParams()['email'],
                    'cpf' => $request->getQueryParams()['document'],
                    'telefone' => $request->getQueryParams()['phone'],
                    'endereco' => $request->getQueryParams()['street'],
                    'num' => $request->getQueryParams()['number'],
                    'complemento' => $request->getQueryParams()['complement'],
                    'bairro' => $request->getQueryParams()['neighborhood'],
                    'cidade' => $request->getQueryParams()['city'],
                    'uf' => $request->getQueryParams()['state'],
                    'cep' => $request->getQueryParams()['postCode'],
                    'rede' => $request->getQueryParams()['rede']                        
                ]);
            } else {
                $item = Doadores::where('cpf', $request->getQueryParams()['document'])->first();
            }

            if(!$item){
                echo 'Erro cadastrar dados no banco de doadores!';
            }

            try{
                $chargeService = new Juno\Charge($token);
                $charge = $chargeService->createCharge([
                    'charge' => [
                        'description' => 'Doação',
                        'amount' => $amount,
                        'paymentTypes' => [$request->getQueryParams()['payment_method']],
                    ],
                    'billing' => [
                        'name' => $request->getQueryParams()['name'],
                        'document' => $request->getQueryParams()['document'],
                        'email' => $request->getQueryParams()['email'],
                        'birthDate' => '',
                        'notify' => true
                    ]
                ]);
                // print_r($charge);
            } catch (GuzzleClientException $e) {
                print_r($e->getResponse()->getBody()->getContents());
            }

            if(!$charge){
                echo 'Erro ao gerar cobrança!';
            }

            try{
                $creditCardService = new Juno\CreditCard($token);
                $tokenizedCard = $creditCardService->tokenizeCard([
                    'creditCardHash' => $request->getQueryParams()['creditCardHash']
                ]);
                // print_r($tokenizedCard);
            } catch (GuzzleClientException $e) {
                print_r($e->getResponse()->getBody()->getContents());
            }
            
            if(!$tokenizedCard){
                echo 'Erro ao gerar token do cartão!';
            }

            try{
                $paymentService = new Juno\Payment($token);
                $payment = $paymentService->createPayment([
                    'chargeId' => $charge->_embedded->charges[0]->id,
                    'billing' => [
                        'email' => $request->getQueryParams()['email'],
                        'address' => [
                            'street' => $request->getQueryParams()['street'],
                            'number' => $request->getQueryParams()['number'],
                            'complement' => $request->getQueryParams()['complement'],
                            'neighborhood' => $request->getQueryParams()['neighborhood'],
                            'city' => $request->getQueryParams()['city'],
                            'state' => $request->getQueryParams()['state'],
                            'postCode' => str_replace('-', '', $request->getQueryParams()['postCode']),
                        ],
                        'delayed' => false
                    ],
                    'creditCardDetails' => [
                        'creditCardId' => $tokenizedCard->creditCardId
                    ]
                ]);
                // print_r($payment);
            } catch (GuzzleClientException $e) {
                print_r($e->getResponse()->getBody()->getContents());
            }

            if(!$payment){
                echo 'Erro ao obter informações do pagamento!';
            }

            if($item){
                $cobranca = Charges::create([
                    'doador' => $item->id,
                    'amount' => $amount,
                    'paymentTypes' => $request->getQueryParams()['payment_method'],
                    'chargeId' => $charge->_embedded->charges[0]->id,
                    'creditCardId' => $tokenizedCard->creditCardId,
                    'chargeStatus' => $charge->_embedded->charges[0]->status,
                    'paymentStatus' => $payment->payments[0]->status
                ]);
            } else{
                echo 'Erro ao salvar cobrança no banco de dados!';
            }
                
            if($payment->payments[0]->status['CONFIRMED']){
                echo 'https://renovacaopara.com.br/obrigado.php';
            }

            if($payment->status >= 400){
                echo $payment->details[0]->message;
            }
            
        }
    }

    public function getCharge($request, $response, $args)
    {
        $token = array(
            'PRIVATE_TOKEN' => PRIVATE_TOKEN_PRODUCAO,
            'CLIENT_ID' => CLIENT_ID_PRODUCAO,
            'CLIENT_SECRET' => CLIENT_SECRET_PRODUCAO
        ); 

        $item = Charges::find($args['id']);

        try {
            $chargeService = new Juno\Charge($token);
            $charge = $chargeService->getCharge($item->chargeId);
            print_r($charge);
        } catch (GuzzleClientException $e) {
            print_r($e->getResponse()->getBody()->getContents());
        }
    }

    public function getCharges($request, $response, $args)
    {
        $token = array(
            'PRIVATE_TOKEN' => PRIVATE_TOKEN_PRODUCAO,
            'CLIENT_ID' => CLIENT_ID_PRODUCAO,
            'CLIENT_SECRET' => CLIENT_SECRET_PRODUCAO
        ); 

        $item = Charges::orderBy('created_at', 'DESC')->get()->toArray();

        for ($i=0; $i < count($item); $i++) { 
            $id_cobranca = $item[$i]['id'];

            $doador[$id_cobranca] = Doadores::find($item[$i]['doador']);
            $this->view->offsetSet("doador", $doador);

            $rede[$id_cobranca] = Redes::where('link', $doador[$id_cobranca]['rede'])->first();
            $this->view->offsetSet("rede", $rede);

            try {
                $chargeService = new Juno\Charge($token);
                $charge = $chargeService->getCharge($item[$i]['chargeId']);
                
                if(isset($charge->payments[0]->status)){
                    $pagamento[$id_cobranca] = $charge->payments[0]->status;
                }

                $this->view->offsetSet("pagamento", $pagamento);

                $data_vencimento[$id_cobranca] = $charge->dueDate;
                $this->view->offsetSet("data_vencimento", $data_vencimento);

                $status[$id_cobranca] = $charge->status;
                $this->view->offsetSet("status", $status);

                // ATUALIZA AS INFORMAÇÕES DA COBRANÇA
                $cobranca = Charges::find($id_cobranca);
                if($cobranca['chargeStatus'] == 'ACTIVE'){
                    $cobranca->update([
                        'chargeStatus' => $charge->status,
                        'paymentStatus' => $charge->payments[0]->status
                    ]);
                }
            } catch (GuzzleClientException $e) {
                print_r($e->getResponse()->getBody()->getContents());
            }
        }        

        $mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'doacoes/cobrancas/listar.html', [
            'Titulo_Pagina' => 'Cobranças',
            'titulo'    => 'Listagem das cobranças',
            'subtitulo' => 'Todas as cobranças geradas pelo sistema de doações',
            'view'      => 'listar',
            'flash'     => $mensagem,
            'itens'     => $item,
        ]);
    }

    public function planos($request, $response, $args){
        // listar plano {id} 
        // try {
        //     $planService = new Juno\Plan($token);
        //     $plan = $planService->getPlan('pln_DEE650FA83376217');
        //     print_r($plan);
        // } catch (GuzzleClientException $e) {
        //     print_r($e->getResponse()->getBody()->getContents());
        // }

        $token = array(
            'PRIVATE_TOKEN' => PRIVATE_TOKEN_PRODUCAO,
            'CLIENT_ID' => CLIENT_ID_PRODUCAO,
            'CLIENT_SECRET' => CLIENT_SECRET_PRODUCAO
        );

        try {
            $planService = new Juno\Plan($token);
            $plan = $planService->getPlans();
        } catch (GuzzleClientException $e) {
            print_r($e->getResponse()->getBody()->getContents());
        }

        $mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'doacoes/planos/listar.html', [
            'Titulo_Pagina' => 'Planos',
            'titulo'    => 'Listagem dos planos de assinaturas',
            'subtitulo' => 'Todos os planos de assinaturas cadastrados na Juno',
            'view'      => 'listar',
            'flash'     => $mensagem,
            'itens'     => $plan->_embedded->plans,
        ]);
    }

    public function createPlan($request, $response, $args){
        $token = array(
            'PRIVATE_TOKEN' => PRIVATE_TOKEN_PRODUCAO,
            'CLIENT_ID' => CLIENT_ID_PRODUCAO,
            'CLIENT_SECRET' => CLIENT_SECRET_PRODUCAO
        );

        try {
            $planService = new Juno\Plan($token);
            $plan = $planService->createPlan([
                'name' => $request->getParam('nome'),
                'amount' => $request->getParam('valor'),
            ]);

            if($plan){
                $this->flash->addMessage('success', 'Plano cadastrado com sucesso!');
            } else {
                $this->flash->addMessage('error', 'Erro ao cadastrar plano!');
            }
        } catch (GuzzleClientException $e) {
            print_r($e->getResponse()->getBody()->getContents());
        }

        return $response->withRedirect($this->router->pathFor('planos'));
    }

    public function assinaturas($request, $response, $args){
        // listar assinatura {id} 
        // try {
        //     $subscriptionService = new Juno\Subscription($token);
        //     $subscription = $subscriptionService->getSubscriptions();
        //     $result = json_decode($subscription);
        //     print_r($subscription->_embedded->subscriptions[0]);
        // } catch (GuzzleClientException $e) {
        //     print_r($e->getResponse()->getBody()->getContents());
        // }

        $token = array(
            'PRIVATE_TOKEN' => PRIVATE_TOKEN_PRODUCAO,
            'CLIENT_ID' => CLIENT_ID_PRODUCAO,
            'CLIENT_SECRET' => CLIENT_SECRET_PRODUCAO
        );

        try {
            $subscriptionService = new Juno\Subscription($token);
            $subscription = $subscriptionService->getSubscriptions();
            $result = json_decode($subscription);
        } catch (GuzzleClientException $e) {
            print_r($e->getResponse()->getBody()->getContents());
        }

        $mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'doacoes/assinaturas/listar.html', [
            'Titulo_Pagina' => 'Assinaturas',
            'titulo'    => 'Listagem das assinaturas de doação',
            'subtitulo' => 'Todas as assinaturas de doação cadastradas na Juno',
            'view'      => 'listar',
            'flash'     => $mensagem,
            'itens'     => $subscription->_embedded->subscriptions,
        ]);
    }

    public function createSubscription($request, $response, $args){
        $token = array(
            'PRIVATE_TOKEN' => PRIVATE_TOKEN_PRODUCAO,
            'CLIENT_ID' => CLIENT_ID_PRODUCAO,
            'CLIENT_SECRET' => CLIENT_SECRET_PRODUCAO
        );

        try {
            $planService = new Juno\Plan($token);
            $plan = $planService->getPlans();
        } catch (GuzzleClientException $e) {
            print_r($e->getResponse()->getBody()->getContents());
        }

        // if($request->getQueryParams()['amount'] != ''){
        //     $amount = $request->getQueryParams()['amount'];
        // } else {
        //     $res = ['status' => 'erro', 'msg' => 'Selecione o valor da doação'];
        //     print_r($res);
        // }

        if($request->getQueryParams()['amount'] != ''){
            $amount = $request->getQueryParams()['amount'];
        }

        if($request->getQueryParams()['other_amount'] != ''){
            $amount = $request->getQueryParams()['other_amount'];
        }

        for ($i=0; $i < count($plan->_embedded->plans) ; $i++) { 
            if($plan->_embedded->plans[$i]->amount == $amount && $plan->_embedded->plans[$i]->status == 'ACTIVE'){
                try {
                    // $creditCardService = new Juno\CreditCard($token);
                    // $tokenizedCard = $creditCardService->tokenizeCard([
                    //     'creditCardHash' => $request->getQueryParams()['creditCardHash']
                    // ]);

                    $subscriptionService = new Juno\Subscription($token);
                    $subscription = $subscriptionService->createSubscription([
                        "dueDay" => date('d'),
                        "planId" => $plan->_embedded->plans[$i]->id,
                        "chargeDescription" => $plan->_embedded->plans[$i]->name,
                        "creditCardDetails" => [
                            "creditCardHash" => $request->getQueryParams()['creditCardHash'],
                        ],
                        "billing" => [
                            "name" => $request->getQueryParams()['name'],
                            "document" => $request->getQueryParams()['document'],
                            "email" => $request->getQueryParams()['email'],
                            "address" => [
                                "street" => $request->getQueryParams()['street'],
                                "number" => $request->getQueryParams()['number'],
                                "complement" => $request->getQueryParams()['complement'],
                                "neighborhood" => $request->getQueryParams()['neighborhood'],
                                "city" => $request->getQueryParams()['city'],
                                "state" => $request->getQueryParams()['state'],
                                "postCode" => str_replace('-', '', $request->getQueryParams()['postCode'])
                            ],
                            "notify" => true,
                        ],
                    ]);

                    if($subscription){
                        $doador = Doadores::where('cpf', $request->getQueryParams()['document'])->count();
                        if($doador < 1){
                            $item = Doadores::create([
                                'nome' => $request->getQueryParams()['name'],
                                'email' => $request->getQueryParams()['email'],
                                'cpf' => $request->getQueryParams()['document'],
                                'telefone' => $request->getQueryParams()['phone'],
                                'endereco' => $request->getQueryParams()['street'],
                                'num' => $request->getQueryParams()['number'],
                                'complemento' => $request->getQueryParams()['complement'],
                                'bairro' => $request->getQueryParams()['neighborhood'],
                                'cidade' => $request->getQueryParams()['city'],
                                'uf' => $request->getQueryParams()['state'],
                                'cep' => str_replace('-', '', $request->getQueryParams()['postCode']),
                                'rede' => $request->getQueryParams()['rede']                        
                            ]);
                        } else {
                            $item = Doadores::where('cpf', $request->getQueryParams()['document'])->first();
                        }
                        
                        if($subscription->status['ACTIVE']){
                            echo 'https://renovacaopara.com.br/obrigado.php';
                        }

                    } else {
                        echo "Erro ao efetuar doação recorrente!";
                    }

                } catch (GuzzleClientException $e) {
                    print_r($e->getResponse()->getBody()->getContents());
                }
            }
        }

        // try {
        //     $subscriptionService = new Juno\Subscription($token);
        //     $subscription = $subscriptionService->createSubscription([
        //         "dueDay" => date('d'),
        //         "planId" => "pln_DEE650FA83376217",
        //         "chargeDescription" => "Doação",
        //         "creditCardDetails" => [
        //             "creditCardHash" => "284ec9f0-03d4-422d-958b-b97f2f6d6825"
        //         ],
        //         "billing" => [
        //             "name" => "Gabriel Bontorin Calbente",
        //             "document" => "05191936957",
        //             "email" => "gabrielbontorin@live.com",
        //             "address" => [
        //                 "street" => "Rua Domingos Fernandes Maia",
        //                 "number" => "581",
        //                 "complement" => "MD 03",
        //                 "neighborhood" => "Bairro Alto",
        //                 "city" => "Curitiba",
        //                 "state" => "PR",
        //                 "postCode" => "82820430"
        //             ],
        //         ],
        //     ]);
        //     print_r($subscription);
        // } catch (GuzzleClientException $e) {
        //     print_r($e->getResponse()->getBody()->getContents());
        // }

        // return $response->withRedirect($this->router->pathFor('planos'));
    }
}