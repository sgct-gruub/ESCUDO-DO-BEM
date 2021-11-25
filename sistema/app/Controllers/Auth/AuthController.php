<?php

namespace App\Controllers\Auth ;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use App\Controllers\Controller ;
use Respect\Validation\Validator as validate;
use Slim\Views\Twig as View;

use App\Models\Users;

class AuthController extends Controller
{
/*
* singin
*
*/

     // get
     public function getSingin(Request $request, Response $response) {
       return $this->view->render($response, 'Auth/login.html');
     }
     // Post
     public function postSingin(Request $request, Response $response) {
      
         $auth = $this->Auth->userExiste($request->getParam('email'),$request->getParam('password'));
          
          if(!$auth){

            $this->flash->addMessageNow('error', 'Usuário ou senha incorretos.');
            $this->view->offsetSet("flash", $this->flash->getMessages());

            return $this->view->render($response, 'Auth/login.html');
          } else {
            return $response->withRedirect($this->router->pathFor('home'));
          }
      }
/*
* Register
*
*/

 // get
    public function getRegister($request,$response)
    {
      return $this->view->render($response, 'Auth/singup.twig');
    }

// post
    public function postRegister($request,$response)
    {

      $validation=$this->Validator->validate($request,[
         'email'=>validate::noWhitespace()->NotEmpty()->Email(),
         'name'=>validate::noWhitespace()->NotEmpty()->Alpha(),
         'password'=>validate::noWhitespace()->NotEmpty(),
      ]);

      if($validation->failed()){
        return $response->withRedirect($this->router->pathFor('register'));
      }

     Users::create([
       'name' => $request->getParam('name'),
       'email' => $request->getParam('email'),
       'password' => password_hash($request->getParam('password'),PASSWORD_DEFAULT),
     ]);
     return $response->withRedirect($this->router->pathFor('home'));
    }

    // logout
    public function logout($request,$response)
     {
        unset($_SESSION['Unidade']);
        $auth=$this->Auth->logout();
        return $response->withRedirect($this->router->pathFor('login'));
     }

}
