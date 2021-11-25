<?php

namespace App\Auth;
use App\Models\Users;
use App\Models\Roles;

class Auth {

  public function user() {
    $user = Users::find($_SESSION["Auth"]);
    return $user;
  }

  public function logout() {
    unset($_SESSION["Auth"]);
    unset($_SESSION["Role"]);
    unset($_SESSION["UserName"]);
    unset($_SESSION["UserEmail"]);
  }

  public function userExiste($email, $password) {
    $user = Users::where('email', $email)->first();
    if (!$user) {
      return false;
    }

    if (password_verify($password, $user->password)) {
      $_SESSION["Auth"] = $user->id;
      $_SESSION["Role"] = $user->role;
      $_SESSION["UserName"] = $user->name;
      $_SESSION["UserEmail"] = $user->email;
      return true;
    }
    
  }

  public function userCheck() {
    return isset($_SESSION["Auth"]);
  }
}
