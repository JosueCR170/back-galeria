<?php

namespace App\Http\Controllers;

use App\Helpers\JwtAuth;
use App\Models\Artista;
use Illuminate\Http\Request;

class ArtistaController
{
    //

    public function index()
    {
        $data = Artista::all();
        $response = array(
            "status" => 200,
            "message" => "Todos los registros de Artistas",
            "data" => $data
        );
        return response()->json($response, 200);
    }

    public function show(Request $request, $id)
    {
            $data = Artista::find($id);
            if (is_object($data)) {
                $response = array(
                    'status' => 200,
                    'message' => 'Datos del Artista',
                    'Artista' => $data
                );
            } else {
                $response = array(
                    'status' => 404,
                    'message' => 'Recurso no encontrado'
                );
            }
        return response()->json($response, $response['status']);
    }

    public function store(Request $request)
    {
        $data_input = $request->input('data', null);
        if ($data_input) {
            $data = json_decode($data_input, true);
            $data = array_map('trim', $data);
            $rules = [
                'nombre' => 'required|string|max:80',
                'password' => 'required|max:20',
                'telefono' => 'required|numeric|unique:Artista',
                'correo' => 'required|email|unique:Artista|max:45',
                'nombreArtista' => 'required|unique:Artista|max:45',
            ];
            $isValid = \validator($data, $rules);
            if (!$isValid->fails()) {
                $artista = new Artista();
                $artista->nombre = $data['nombre'];
                $artista->password = hash('sha256', $data['password']);
                $artista->telefono = $data['telefono'];
                $artista->correo = $data['correo'];
                $artista->nombreArtista = $data['nombreArtista'];

                $artista->save();
                $response = array(
                    'status' => 201,
                    'message' => 'Artista creado',
                    'Artista' => $artista
                );
            } else {
                $response = array(
                    'status' => 406,
                    'message' => 'Datos inválidos',
                    'errors' => $isValid->errors()
                );
            }
        } else {
            $response = array(
                'status' => 400,
                'message' => 'No se encontró el objeto data'
            );
        }
        return response()->json($response, $response['status']);
    }

    public function update(Request $request, $id)
    {
        $authAccess=false;
        $jwt = new JwtAuth();
        $decodedToken = $jwt->checkToken($request->header('bearertoken'), true);
        $adminVerified = isset($decodedToken->tipoUsuario) ? $decodedToken->tipoUsuario : null;
        $artistaVerified = isset($decodedToken->nombreArtista) ? $decodedToken->nombreArtista : null;


        if (($artistaVerified !==null && $decodedToken->iss===$id) || ($adminVerified!==null)) {
            $authAccess = true;
        }
        
        if ($authAccess) {
            $artista = Artista::find($id);
            if (!$artista) {
                $response = [
                    'status' => 404,
                    'message' => 'Artista no encontrado'
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
                'password' => 'max:20',
                'telefono' => 'max:11',
                'nombreArtista' => 'unique:Artista|max:45',
            ];

            $validator = \validator($data_input, $rules);

            if ($validator->fails()) {
                $response = [
                    'status' => 406,
                    'message' => 'Datos inválidos',
                    'error' => $validator->errors()
                ];
                return response()->json($response, $response['status']);
            }

            if (isset($data_input['nombre'])) {
                $artista->nombre = $data_input['nombre'];
            }
            if (isset($data_input['password'])) {
                $artista->password = hash('sha256', $data_input['password']);
            }
            if (isset($data_input['telefono'])) {
                $artista->telefono = $data_input['telefono'];
            }
            if (isset($data_input['nombreArtista'])) {
                $artista->nombreArtista = $data_input['nombreArtista'];
            }

            $artista->save();

            $response = [
                'status' => 201,
                'message' => 'Artista actualizado',
                'Artista' => $artista
            ];
        } else {
            $response = array(
                'status' => 406,
                'menssage' => 'No tienes acceso al recurso'
            );
        }
        return response()->json($response, $response['status']);
    }

    public function destroy(Request $request, $id)
    {
        $jwt = new JwtAuth();
        if ($jwt->checkToken($request->header('bearertoken'), true)->tipoUsuario) {
            if (isset($id)) {
                $deleted = Artista::where('id', $id)->delete();
                if ($deleted) {
                    $response = array(
                        'status' => 200,
                        'message' => 'Artista eliminado',
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

    public function login(Request $request)
    {
        $data_input = $request->input('data', null);
        $data = json_decode($data_input, true);
        $data = array_map('trim', $data);
        $rules = ['correo' => 'required', 'password' => 'required'];
        $isValid = \validator($data, $rules);
        if (!$isValid->fails()) {
            $jwt = new JwtAuth();
            $response = $jwt->getTokenArtista($data['correo'], $data['password']);
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
                'message' => 'Token (bearertoken) no encontrado',
            );
        }
        //var_dump(response()->json($response));
        return response()->json($response);
    }
}
