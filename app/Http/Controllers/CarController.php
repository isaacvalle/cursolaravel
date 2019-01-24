<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\JwtAuth; // helper de JWTAuth que se hizo en los provides
use App\Car; //Se importa el modelo de carro

class CarController extends Controller
{
  public function index(Request $request) {
    $hash = $request->header('Authorization', null);

    $jwtAuth = new JwtAuth();
    $checkToken = $jwtAuth->checkToken($hash);

    if($checkToken) {
      echo "Index de CarController correctamente autenticado";
    }else {
      echo "Index de CarController NO autenticado";
    }
  }

  public function store(Request $request) {
    $hash = $request->header('Authorization', null);

    $jwtAuth = new JwtAuth();
    $checkToken = $jwtAuth->checkToken($hash);

    if($checkToken) {
      //Recoger los datos por POST
      $json = $request->input('json', null);
      $params = json_decode($json);
      $params_array = json_decode($json, true);
      //conseguir el usuario identificado
      $user = $jwtAuth->checkToken($hash, true);
      //Si el oken es valido seguarda el coche
      $request->merge($params_array);
      try {
        $validate = $this->validate($request, [
          'title' => 'required|min:5',
          'description' => 'required',
          'price' => 'required',
          'status' => 'required'
        ]);
      }catch(\Illuminate\Validation\ValidationException $e) {
        return $e->getResponse();
      }

      $car = new Car();
      $car->user_id = $user->sub;
      $car->title = $params->title;
      $car->description = $params->description;
      $car->price = $params->price;
      $car->status = $params->status;

      $car->save();

      $data = array(
        'car' => $car,
        'status' => 'success',
        'code' => 200
      );
    }else {
      // notificar error porque no exite el token en la petición
      $data = array(
        'status' => 'success',
        'code' => 300,
        'message' => 'Login incorrecto'
      );
    }
    return response()->json($data, 200);
  }
}
