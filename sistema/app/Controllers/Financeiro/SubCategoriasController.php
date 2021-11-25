<?php

namespace App\Controllers\Financeiro;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use App\Controllers\Controller;
use Respect\Validation\Validator as validate;
use Slim\Views\Twig as View;

use App\Models\Categorias;
use App\Models\Financeiro\SubCategorias;

class SubCategoriasController extends Controller
{
      // EXIBE A LISTAGEM
      public function listar ($request, $response, $args)
      {   
            if($this->acesso_usuario['financeiro']['categorias']['r'] != 'on'){
                  return $this->container->view->render($response->withStatus(403), 'error/403.html');
            }

            $item = SubCategorias::where('categoria', $args['id'])->get()->toArray();
            $categoria = Categorias::find($args['id']);

            // for ($i=0; $i < count($item) ; $i++) { 
            //       $id_categoria = $item[$i]['categoria'];
            //       $id_subcategoria = $item[$i]['id'];
            //       $categoria[$id_subcategoria] = Categorias::find($id_categoria);

            //       $this->view->offsetSet("categoria", $categoria);
            // }

            $mensagem = $this->flash->getMessages();

            return $this->view->render($response, 'financeiro/subcategorias/listar.html', [
                  'Titulo_Pagina_Mae' => 'Financeiro -',
                  'Titulo_Pagina' => 'SubCategorias',
                  'titulo'    => $categoria['nome'],
                  'subtitulo' => 'Listagem das subcategorias',
                  'view'      => 'listar',
                  'flash'     => $mensagem,
                  'itens'     => $item,
                  'categoria' => $categoria
            ]);
      }

      // SALVA O REGISTRO NO BANCO DE DADOS
      public function postCadastrar ($request, $response, $args)
      {
            $item = SubCategorias::create([
                  'nome' => $request->getParam('nome'),
                  'categoria' => $request->getParam('categoria'),
            ]);

            if($item)
            {
                  return true;
            }
            else
            {
                  return false;
            }
      }
}