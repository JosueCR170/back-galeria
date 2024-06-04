<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Helpers\JwtAuth;

class UserController
{
    public function index(Request $request)
    {
        $jwt = new JwtAuth();
        if (!$jwt->checkToken($request->header('bearertoken'), true)->tipoUsuario) {
            $response = array(
                'status' => 406,
                'menssage' => 'No tienes permiso de administrador'

            );
        } else {
            $data = User::all();
            $response = array(
                "status" => 200,
                "message" => "Todos los registros de los usuarios",
                "data" => $data
            );
        }
        return response()->json($response, 200);
    }

    public function store(Request $request)
    {
        $data_input = $request->input('data', null);
        if ($data_input) {
            $data = json_decode($data_input, true);
            if ($data !== null) {
                $data = array_map('trim', $data);
                $rules = [
                    'nombre' => 'required|string|max:80',
                    'telefono' => 'nullable|numeric',
                    'email' => 'required|email|unique:users,email',
                    'password' => 'required|alpha_dash',
                    'nombreUsuario' => 'required|string|max:45|unique:users,nombreUsuario',
                ];
                $validator = validator($data, $rules);
                if (!$validator->fails()) {
                    $user = new User();
                    $user->nombre = $data['nombre'];
                    $user->telefono = is_null($data['telefono']) ? null : (int)$data['telefono'];
                    // $user->telefono = $data['telefono'];
                    $user->email = $data['email'];
                    $user->password = hash('sha256', $data['password']);
                    $user->nombreUsuario = $data['nombreUsuario'];
                    $user->tipoUsuario = false;
                    $user->save();
                    $response = [
                        'status' => 201,
                        'message' => 'Usuario creado exitosamente',
                        'user' => $user
                    ];
                } else {
                    $response = [
                        'status' => 406,
                        'message' => 'Datos inválidos',
                        'error' => $validator->errors()
                    ];
                }
            } else {
                $response = [
                    'status' => 400,
                    'message' => 'No se proporcionaron datos válidos',
                ];
            }
        } else {
            $response = [
                'status' => 400,
                'message' => 'No se encontró el objeto de datos (data)'
            ];
        }

        // Devolver la respuesta JSON
        return response()->json($response, $response['status']);
    }

    public function show(Request $request, $id)
    {
        $jwt = new JwtAuth();
        if (!$jwt->checkToken($request->header('bearertoken'), true)->tipoUsuario) {
            $response = array(
                'status' => 406,
                'menssage' => 'No tienes permiso de administrador'

            );
        } else {
        $data = User::find($id);
        if (is_object($data)) {
            $response = array(
                'status' => 200,
                'message' => 'Datos del usuario',
                'user' => $data
            );
        } else {
            $response = array(
                'status' => 404,
                'message' => 'Recurso no encontrado'
            );
        }
    }
        return response()->json($response, $response['status']);
    }

    public function destroy(Request $request, $id)
    {
        $jwt = new JwtAuth();
        if ($jwt->checkToken($request->header('bearertoken'), true)->tipoUsuario) {
            if (isset($id)) {
                $deleted = User::where('id', $id)->delete();
                if ($deleted) {
                    $response = array(
                        'status' => 200,
                        'message' => 'Usuario eliminado'
                    );
                } else {
                    $response = array(
                        'status' => 400,
                        'message' => 'No se pudo eliminar el recurso, compruebe que exista'
                    );
                }
            } else {
                $response = array(
                    'status' => 406,
                    'message' => 'Falta el identificador del recurso a eliminar'
                );
            }
        } else {
            $response = array(
                'status' => 406,
                'menssage' => 'No tienes permiso de administrador'

            );
        }
        return response()->json($response, $response['status']);
    }

    //posible error al actualizar telefono, verlo despues
    public function update(Request $request, $id)
{
    $jwt = new JwtAuth();
    $sessionUser = $jwt->checkToken($request->header('bearertoken'), true);
    $user = User::find($id);
    if (!$user) {
        $response = [
            'status' => 404,
            'message' => 'Usuario no encontrado'
        ];
        return response()->json($response, $response['status']);
    }
    if (!$sessionUser->tipoUsuario && $sessionUser->iss != $id) {
        $response = [
            'status' => 403,
            'message' => 'No tienes permiso para actualizar este usuario'
        ];
        return response()->json($response, $response['status']);
    }

    $data_input = $request->input('data', null);
    $data_input = json_decode($data_input, true);

    if (!$data_input) {
        $response = [
            'status' => 400,
            'message' => 'No se encontró el objeto data. No hay datos que modificar'
        ];
        return response()->json($response, $response['status']);
    }

    $rules = [
        'nombre' => 'string|max:80',
        'telefono' => 'nullable|numeric',
        'password' => 'alpha_dash',
        'nombreUsuario' => 'string|max:80'
    ];

    if ($sessionUser->tipoUsuario) {
        $rules['tipoUsuario'] = 'boolean';
    }

    $validator = \Validator::make($data_input, $rules);

    if ($validator->fails()) {
        $response = [
            'status' => 406,
            'message' => 'Datos inválidos',
            'error' => $validator->errors()
        ];
        return response()->json($response, $response['status']);
    }

    if (isset($data_input['nombre'])) {
        $user->nombre = $data_input['nombre'];
    }
    if (isset($data_input['telefono'])) {
        $user->telefono = $data_input['telefono'];
    }
    if (isset($data_input['password'])) {
        $user->password = hash('sha256', $data_input['password']);
    }
    if (isset($data_input['nombreUsuario'])) {
        $user->nombreUsuario = $data_input['nombreUsuario'];
    }

    if ($sessionUser->tipoUsuario && isset($data_input['tipoUsuario'])) {
        $user->tipoUsuario = $data_input['tipoUsuario'];
    }

    $user->save();

    $response = [
        'status' => 201,
        'message' => 'Usuario actualizado',
        'user' => $user
    ];

    return response()->json($response, $response['status']);
}


    public function login(Request $request)
    { //listo
        $data_input = $request->input('data', null);
        $data = json_decode($data_input, true);


        if ($data !== null) {
            $data = array_map('trim', $data);
        } else {

            $response = array(
                'status' => 400,
                'message' => 'No se proporcionaron datos válidos',
            );
            return response()->json($response, 400);
        }

        $rules = ['nombreUsuario' => 'required', 'password' => 'required'];
        $isValid = \validator($data, $rules);

        if (!$isValid->fails()) {
            $jwt = new JwtAuth();
            $response = $jwt->getTokenUser($data['nombreUsuario'], $data['password']);
            return response()->json($response);
        } else {
            $response = array(
                'status' => 406,
                'message' => 'Error en la validación de los datos',
                'errors' => $isValid->errors(),
            );
            return response()->json($response, 406);
        }
    }

    public function getIdentity(Request $request)
    {
        $jwt = new JwtAuth();
        $token = $request->header('bearertoken');
        if (isset($token)) {
            $response = $jwt->checkToken($token, true);
        } else {
            $response = array(
                'status' => 404,
                'message' => 'token (bearertoken) no encontrado',
            );
        }
        return response()->json($response);
    }
}
