<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Helpers\JwtAuth;

class UserController
{
    public function show(Request $request, $id)
    {
        $jwt = new JwtAuth();
        if (!$jwt->checkToken($request->header('bearertoken'), true)->tipoUsuario) {
            $response = array(
                'status' => 406,
                'menssage' => 'No tienes permiso de administrador'
            );
        } else {
            $data = DB::select('EXEC paBuscarUsuario ?', [$id]);
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
                    $nombre = $data['nombre'];
                    $telefono = is_null($data['telefono']) ? null : (int) $data['telefono'];
                    // $user->telefono = $data['telefono'];
                    $email = $data['email'];
                    $password = hash('sha256', $data['password']);
                    $nombreUsuario = $data['nombreUsuario'];
                    $tipoUsuario = $data['tipoUsuario'];
                    DB::statement(
                        'EXEC paInsertarUsuario ?, ?, ?, ?, ?, ?',
                        [$nombre, $password, $telefono, $email, $tipoUsuario, $nombreUsuario]
                    );
                    $response = [
                        'status' => 201,
                        'message' => 'Usuario creado exitosamente'
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
        return response()->json($response, $response['status']);
    }



    public function index(Request $request)
    {
        $jwt = new JwtAuth();
        if (!$jwt->checkToken($request->header('bearertoken'), true)->tipoUsuario) {
            $response = array(
                'status' => 406,
                'menssage' => 'No tienes permiso de administrador'
            );
        } else {
            $data = DB::select('select id, nombre, telefono, email, tipoUsuario, nombreUsuario from vMostrarTodosUsuarios');
            $response = array(
                "status" => 200,
                "message" => "Todos los registros de los usuarios",
                "data" => $data
            );
        }
        return response()->json($response, 200);
    }

    public function destroy(Request $request, $id)
    {
        $jwt = new JwtAuth();

        if ($jwt->checkToken($request->header('bearertoken'), true)->tipoUsuario) {
            if (isset($id)) {
                try {
                    DB::beginTransaction();

                    DB::statement('EXEC paEliminarUsuario ?', [$id]);
                    $userStillExists = DB::table('users')->where('id', $id)->exists();

                    if ($userStillExists) {
                        throw new \Exception('The user could not be deleted due to foreign key restrictions or other errors');
                    }

                    DB::commit();

                    $response = [
                        'status' => 200,
                        'message' => 'Usuario eliminado correctamente'
                    ];

                } catch (\Illuminate\Database\QueryException $e) {
                    DB::rollBack();

                    $sqlErrorMessage = isset($e->errorInfo[2]) ? $e->errorInfo[2] : $e->getMessage();

                    $response = [
                        'status' => 500,
                        'message' => 'Error al ejecutar el procedimiento de eliminación',
                        'error' => $sqlErrorMessage
                    ];
                } catch (\Exception $e) {
                    DB::rollBack();

                    $response = [
                        'status' => 500,
                        'message' => 'Error al eliminar el usuario',
                        'error' => $e->getMessage()
                    ];
                }
            } else {
                $response = [
                    'status' => 406,
                    'message' => 'Falta el identificador del recurso a eliminar'
                ];
            }
        } else {
            $response = [
                'status' => 403,
                'message' => 'No tienes permiso de administrador'
            ];
        }

        return response()->json($response, $response['status']);
    }
    

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
            $nombre = $data_input['nombre'];
        } else {
            $nombre = $user->nombre;
        }
        if (isset($data_input['telefono'])) {
            $telefono = $data_input['telefono'];
        } else {
            $telefono = $user->telefono;
        }
        if (isset($data_input['password']) || !empty($data_input['password'])) {
            $password = hash('sha256', $data_input['password']);
        } else {
            $password = $user->password;
        }
        if (isset($data_input['nombreUsuario'])) {
            $nombreUsuario = $data_input['nombreUsuario'];
        } else {
            $nombreUsuario = $user->nombreUsuario;
        }
        if (isset($data_input['email'])) {
            $email = $data_input['email'];
        } else {
            $email = $user->email;
        }
        if (isset($data_input['nombreUsuario'])) {
            $nombreUsuario = $data_input['nombreUsuario'];
        } else {
            $nombreUsuario = $user->nombreUsuario;
        }
        if ($sessionUser->tipoUsuario && isset($data_input['tipoUsuario'])) {
            $tipoUsuario = $data_input['tipoUsuario'];
        } else {
            $tipoUsuario = $user->tipoUsuario;
        }

        DB::statement('EXEC paActualizarUsuario ?, ?, ?, ?, ?, ?, ?', [$id, $nombre, $password, $telefono, $email, $tipoUsuario, $nombreUsuario]);

        $response = [
            'status' => 201,
            'message' => 'Usuario actualizado'
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