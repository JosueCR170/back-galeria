<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Matricula;
use App\Helpers\JwtAuth;

class MatriculaController
{

    public function show($id)
    {
        $data = DB::select('EXEC paBuscarMatricula ?', [$id]);

        if (is_object($data) && !empty($data)) {
            $response = [
                'status' => 200,
                'message' => 'Datos de la matrícula',
                'matricula' => $data
            ];
        } else {
            $response = [
                'status' => 404,
                'message' => 'Matrícula no encontrada'
            ];
        }
        return response()->json($response, $response['status']);
    }

    public function index()
    {
        $data = DB::select('select * from vMostrarTodosMatriculas');
        $response = [
            'status' => 200,
            'message' => 'Todos los registros de las matrículas',
            'data' => $data
        ];
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
                    'idUsuario' => 'required|numeric|exists:users,id',
                    'idOferta' => 'required|numeric|exists:ofertas,id',
                    'costo' => 'required|numeric',
                    'fechaMatricula' => 'required|date',
                ];
                $validator = validator($data, $rules);
                if (!$validator->fails()) {
                    $idUsuario = (int) $data['idUsuario'];
                    $idOferta = (int) $data['idOferta'];
                    $fechaMatricula = $data['fechaMatricula'];
                    $costo = (float) $data['costo'];

                    DB::statement(
                        'EXEC paInsertarMatricula ?, ?, ?, ?',
                        [$idUsuario, $idOferta, $costo, $fechaMatricula]
                    );

                    $response = [
                        'status' => 201,
                        'message' => 'Matrícula creada exitosamente'
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

    public function destroy(Request $request, $id)
    {
        if (isset($id)) {
            DB::statement('EXEC paEliminarMatricula ?', [$id]);

            $response = [
                'status' => 200,
                'message' => 'Matrícula eliminada exitosamente'
            ];
        } else {
            $response = [
                'status' => 406,
                'message' => 'Falta el identificador de la matrícula a eliminar'
            ];
        }

        return response()->json($response, $response['status']);
    }

    public function update(Request $request, $id)
    {
        $matricula = Matricula::find($id);

        if (!$matricula) {
            $response = [
                'status' => 404,
                'message' => 'Matrícula no encontrada'
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
            'idUsuario' => 'nullable|numeric|exists:users,id',
            'idOferta' => 'nullable|numeric|exists:ofertas,id',
            'costo' => 'required|numeric',
            'fechaMatricula' => 'nullable|date'
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

        $idUsuario = isset($data_input['idUsuario']) ? $data_input['idUsuario'] : $matricula->idUsuario;
        $idOferta = isset($data_input['idOferta']) ? $data_input['idOferta'] : $matricula->idOferta;
        $costo = isset($data_input['costo']) ? $data_input['costo'] : $matricula->costo;
        $fechaMatricula = isset($data_input['fechaMatricula']) ? $data_input['fechaMatricula'] : $matricula->fechaMatricula;

        DB::statement('EXEC paActualizarMatricula ?, ?, ?, ?, ?', [$id, $idUsuario, $idOferta, $costo, $fechaMatricula]);

        $response = [
            'status' => 201,
            'message' => 'Matrícula actualizada exitosamente'
        ];

        return response()->json($response, $response['status']);
    }
}
