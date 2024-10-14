<?php

namespace App\Http\Controllers;
use App\Helpers\JwtAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\taller;
class TallerController
{

    public function show(Request $request, $id)
    {
    $jwt = new JwtAuth();
    if (!$jwt->checkToken($request->header('bearertoken'), true)->tipoUsuario) {
        $response = array(
            'status' => 406,
            'message' => 'No tienes permiso de administrador'
        );
    } else {
        $data = DB::select('EXEC paBuscarTaller ?', [$id]);
        if ($data) {
            $response = array(
                'status' => 200,
                'message' => 'Datos del taller',
                'taller' => $data
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
                    'nombre' => 'required|string|max:50',
                    'descripcion' => 'required|string|max:255',
                    'duracion' => 'required|numeric',
                    'costo' => 'required|numeric',
                ];
                $validator = Validator::make($data, $rules);
                if (!$validator->fails()) {
                    $nombre = $data['nombre'];
                    $descripcion = $data['descripcion'];
                    $duracion = (float) $data['duracion'];
                    $costo = (float) $data['costo'];
                    
                    DB::statement(
                        'EXEC paInsertarTaller ?, ?, ?, ?',
                        [$nombre, $descripcion, $duracion, $costo]
                    );
                    $response = [
                        'status' => 201,
                        'message' => 'Taller creado exitosamente'
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
            'message' => 'No tienes permiso de administrador'
        );
    } else {
        $data = DB::select('select id, nombre, descripcion, duracion, costo from vMostrarTodosTalleres');
        $response = array(
            "status" => 200,
            "message" => "Todos los registros de los talleres",
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
            DB::statement('EXEC paEliminarTaller ?', [$id]);
            $response = array(
                'status' => 200,
                'message' => 'Taller eliminado'
            );
        } else {
            $response = array(
                'status' => 406,
                'message' => 'Falta el identificador del recurso a eliminar'
            );
        }
    } else {
        $response = array(
            'status' => 406,
            'message' => 'No tienes permiso de administrador'
        );
    }
    return response()->json($response, $response['status']);
}



    public function update(Request $request, $id)
    {
        $jwt = new JwtAuth();
        $sessionUser = $jwt->checkToken($request->header('bearertoken'), true);
        $taller = DB::table('talleres')->where('id', $id)->first();
        
        if (!$taller) {
            $response = [
                'status' => 404,
                'message' => 'Taller no encontrado'
            ];
            return response()->json($response, $response['status']);
        }
        if (!$sessionUser->tipoUsuario) {
            $response = [
                'status' => 403,
                'message' => 'No tienes permiso para actualizar este taller'
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
            'nombre' => 'string|max:50',
            'descripcion' => 'string|max:255',
            'duracion' => 'numeric',
            'costo' => 'numeric'
        ];
    
        $validator = \Validator::make($data_input, $rules);
    
        if ($validator->fails()) {
            $response = [
                'status' => 406,
                'message' => 'Datos inválidos',
                'error' => $validator->errors()
            ];
            return response()->json($response, $response['status']);
        }
        $nombre = isset($data_input['nombre']) ? $data_input['nombre'] : $taller->nombre;
        $descripcion = isset($data_input['descripcion']) ? $data_input['descripcion'] : $taller->descripcion;
        $duracion = isset($data_input['duracion']) ? $data_input['duracion'] : $taller->duracion;
        $costo = isset($data_input['costo']) ? $data_input['costo'] : $taller->costo;
    
        DB::statement('EXEC paActualizarTaller ?, ?, ?, ?, ?', [$id, $nombre, $descripcion, $duracion, $costo]);
    
        $response = [
            'status' => 201,
            'message' => 'Taller actualizado'
        ];
    
        return response()->json($response, $response['status']);
    }
    

}
