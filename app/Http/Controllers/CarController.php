<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\JwtAuth; // helper de JWTAuth que se hizo en los provides
use App\Car; //Se importa el modelo de carro

class CarController extends Controller
{
  public function index() {
    $cars = Car::all()->load('user');
    return response()->json(array(
        'cars' => $cars,
        'status' => 'success'
      ), 200);
  }

  public function show($id) {
    if ($car = Car::find($id)) {
        $car = Car::find($id)->load('user');
        return response()->json(array(
            'car' => $car,
            'status' => 'success'
        ), 200);
    }else{
        return response()->json(array(
            'status' => 'error',
            'message' => 'No existe dicho vehículo'
        ), 200);
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
      $validate = \Validator::make($params_array, [
        'title' => 'required|min:5',
        'description' => 'required',
        'price' => 'required',
        'status' => 'required'
      ]);

      if($validate->fails()) {
        return response()->json($validate->errors(), 400);
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
        'code' => 200,
        'status_id' => 1
      );
    }else {
      // notificar error porque no exite el token en la petición
      $data = array(
        'status' => 'error',
        'code' => 300,
        'message' => 'Login incorrecto'
      );
    }
    return response()->json($data, 200);
  }

  public function update($id, Request $request) {
    $hash = $request->header('Authorization', null);

    $jwtAuth = new JwtAuth();
    $checkToken = $jwtAuth->checkToken($hash);

    if($checkToken) {
      //Recoger parametros por POST
      $json = $request->input('json', null);
      $params = json_decode($json);
      $params_array = json_decode($json, true);
      //Validar datos
      $validate = \Validator::make($params_array, [
        'title' => 'required|min:5',
        'description' => 'required',
        'price' => 'required',
        'status' => 'required'
      ]);

      if($validate->fails()) {
        return response()->json($validate->errors(), 400);
      }
      //Actualizar el coche porque el token es valido
      $car = Car::where('id', $id)->update($params_array);
      $data = array(
        'car' => $params,
        'status' => 'success',
        'code' => 200
      );
    }else {
      // notificar error porque no exite el token en la petición
      $data = array(
        'status' => 'error',
        'code' => 300,
        'message' => 'Login incorrecto'
      );
    }
    return response()->json($data, 200);
  }

  public function destroy($id, Request $request) {
    $hash = $request->header('Authorization', null);

    $jwtAuth = new JwtAuth();
    $checkToken = $jwtAuth->checkToken($hash);

    if($checkToken) {
      // comprobar que existe el registro
      $car = Car::find($id);
      // borrar registro
      $car->delete();
      // devolverlo
      $data = array(
        'car' => $car,
        'status' => 'success',
        'code' => 200
      );
    }else {
      // notificar error porque no exite el token en la petición
      $data = array(
        'status' => 'error',
        'code' => 300,
        'message' => 'Login incorrecto'
      );
    }
    return response()->json($data, 200);
  }
}
