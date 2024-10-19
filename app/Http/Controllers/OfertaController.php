<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Helpers\JwtAuth;
use App\Models\Oferta;

class OfertaController
{

    public function show(Request $request, $id)
    {
        $data = DB::select('EXEC paBuscarOfertas ?', [$id]);

        if (is_object($data) && !empty($data)) {
            $response = [
                'status' => 200,
                'message' => 'Detalles de la oferta',
                'data' => $data
            ];
        } else {
            $response = [
                'status' => 404,
                'message' => 'Oferta no encontrada'
            ];
        }

        return response()->json($response, $response['status']);
    }

    public function index(Request $request)
    {
        $data = DB::select('SELECT * FROM vMostrarTodosOfertas');

        if (!empty($data)) {
            $response = [
                'status' => 200,
                'message' => 'Todas las ofertas disponibles',
                'data' => $data
            ];
        } else {
            $response = [
                'status' => 404,
                'message' => 'No se encontraron ofertas'
            ];
        }

        return response()->json($response, $response['status']);
    }


    public function store(Request $request)
    {
        $jwt = new JwtAuth();
        if (!$jwt->checkToken($request->header('bearertoken'), true)->tipoUsuario) {
            return response()->json([
                'status' => 406,
                'message' => 'No tienes permiso de administrador'
            ], 406);
        }

        $data_input = $request->input('data', null);
        if ($data_input) {
            $data = json_decode($data_input, true);
            if ($data !== null) {
                $data = array_map('trim', $data);

                $rules = [
                    'idTaller' => 'required|integer|exists:talleres,id',
                    'fechaInicio' => 'required|date',
                    'fechaFinal' => 'required|date|after_or_equal:fechaInicio',
                    'horaInicio' => 'required|date_format:H:i',
                    'horaFinal' => 'required|date_format:H:i|after_or_equal:horaInicio',
                    'ubicacion' => 'string|max:255',  
                    'modalidad' => 'required|string|max:20',
                    'cupos' => 'required|integer',
                ];
                

                $validator = Validator::make($data, $rules);
                if (!$validator->fails()) {
                    DB::statement(
                        'EXEC paInsertarOferta ?, ?, ?, ?, ?, ?, ?, ?',
                        [
                            $data['idTaller'],
                            $data['fechaInicio'],
                            $data['fechaFinal'],
                            $data['horaInicio'],
                            $data['horaFinal'],
                            $data['ubicacion'],
                            $data['modalidad'],
                            $data['cupos']
                        ]
                    );
                    $response = [
                        'status' => 201,
                        'message' => 'Oferta creada exitosamente'
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

    public function update(Request $request, $id)
    {
        $jwt = new JwtAuth();
        if (!$jwt->checkToken($request->header('bearertoken'), true)->tipoUsuario) {
            return response()->json([
                'status' => 406,
                'message' => 'No tienes permiso de administrador'
            ], 406);
        }

        $data_input = $request->input('data', null);
        $data_input = json_decode($data_input, true);

        if (!$data_input) {
            return response()->json([
                'status' => 400,
                'message' => 'No se encontró el objeto data. No hay datos que modificar'
            ], 400);
        }

        $rules = [
            'idTaller' => 'required|integer|exists:talleres,id',
            'fechaInicio' => 'required|date',
            'fechaFinal' => 'required|date|after_or_equal:fechaInicio',
            'horaInicio' => 'required|date_format:H:i',
            'horaFinal' => 'required|date_format:H:i|after_or_equal:horaInicio',
            'ubicacion' => 'string|max:255',
            'modalidad' => 'required|string|max:20',
            'cupos' => 'required|integer',
        ];

        $validator = Validator::make($data_input, $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 406,
                'message' => 'Datos inválidos',
                'error' => $validator->errors()
            ], 406);
        }

        DB::statement('EXEC paActualizarOferta ?, ?, ?, ?, ?, ?, ?, ?, ?', [
            $id,
            $data_input['idTaller'],
            $data_input['fechaInicio'],
            $data_input['fechaFinal'],
            $data_input['horaInicio'],
            $data_input['horaFinal'],
            $data_input['ubicacion'],
            $data_input['modalidad'],
            $data_input['cupos']
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Oferta actualizada exitosamente'
        ], 200);
    }

    public function destroy(Request $request, $id)
    {
        $jwt = new JwtAuth();
        if ($jwt->checkToken($request->header('bearertoken'), true)->tipoUsuario) {
            if (isset($id)) {
                DB::statement('EXEC paEliminarOferta ?', [$id]);
                $response = [
                    'status' => 200,
                    'message' => 'Oferta eliminada exitosamente'
                ];
            } else {
                $response = [
                    'status' => 406,
                    'message' => 'Falta el identificador de la oferta a eliminar'
                ];
            }
        } else {
            $response = [
                'status' => 406,
                'message' => 'No tienes permiso de administrador'
            ];
        }

        return response()->json($response, $response['status']);
    }

}
