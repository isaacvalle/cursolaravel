<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; //Librería de la base de datos
use App\User; //Importación del controlador USER
use App\Helpers\JwtAuth; // helper de JWTAuth que se hizo en los provides

class UserController extends Controller
{
  public function register(Request $request) {
    // echo "Acción registro"; die();
    // Recoger POST y validar variables
    $json = $request->input('json', null);
    $params = json_decode($json);
    $email = (!is_null($json) && isset($params->email) ? $params->email : null);
    $name = (!is_null($json) && isset($params->name) ? $params->name : null);
    $surname = (!is_null($json) && isset($params->surname) ? $params->surname : null);
    $role = 'ROLE_USER';
    $password  =(!is_null($json) && isset($params->password) ? $params->password : null);

    if(!is_null($email) && !is_null($password) && !is_null($name)) {
      //Se crea el usuario si se validó correctamente
      $user = new User();
      $user->email = $email;
      $user->name = $name;
      $user->surname = $surname;
      $user->role = $role;

      $pwd = hash('sha256', $password);
      $user->password = $pwd;

      //Comprobar usuario duplicado
      $isset_user = User::where('email', '=', $email)->count();
      if($isset_user == 0) {
        //no existe el registro, entonces crea el usuario
        // echo "registro";
        $user->save();

        $data = array(
          'status' => 'success',
          'code' => 200,
          'message' => 'Usuario creado con éxito.'
        );
      }else{
        //el usuario ya existe, no se puede registrar
        // echo "ya existe";
        $data = array(
          'status' => 'error',
          'code' => 400,
          'message' => 'El usuario ya existe, intente con otro correo.'
        );
      }
    }else {
      //Si no se pasa la validación se devuelte una respuesta de ERROR
      $data = array(
        'status' => 'error',
        'code' => 400,
        'message' => 'Usuario no creado.'
      );

    }
    return response()->json($data, 200);
  }

  public function login(Request $request) {
    // echo "Acción login"; die();
    $jwtAuth = new JwtAuth();

    //Recibir los datos por POST
    $json = $request->input('json', null);
    $params = json_decode($json);

    $email = (!is_null($json) && isset($params->email)) ? $params->email : null;
    $password = (!is_null($json) && isset($params->password)) ? $params->password : null;
    $getToken = (!is_null($json) && isset($params->gettoken)) ? $params->gettoken : null;

    //cifrar la contraseña
    $pwd = hash('sha256', $password);
    if (!is_null($email) && !is_null($password) && ($getToken == null || $getToken == 'false')) {
      $signup = $jwtAuth->signup($email, $pwd);

    }else if($getToken != null) {
      // var_dump($getToken);die();
      $signup = $jwtAuth->signup($email, $pwd, $getToken);

    }else {
      $signup = array(
                  'status' => 'error',
                  'code' => 400,
                  'message' => 'Envía tus datos por POST.'
                );
    }

    return response()->json($signup, 200);
  }
}
