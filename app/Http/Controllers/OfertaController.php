<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $data = DB::select('SELECT * FROM vMostrarTodasOfertas');

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
        $data_input = $request->input('data', null);
        if ($data_input) {
            $data = json_decode($data_input, true);
            if ($data !== null) {
                $data = array_map('trim', $data);
                $rules = [
                    'idTaller' => 'required|integer|exists:talleres,id',
                    'fechaInicio' => 'required|date',
                    'fechaFinal' => 'required|date|after_or_equal:fechaInicio',
                    'horaInicio' => 'required|time',
                    'horaFinal' => 'required|time|after_or_equal:horaInicio',
                    'ubicacion' => 'required|string|max:255',
                    'modalidad' => 'required|string|max:20'
                ];
                $validator = validator($data, $rules);
                if (!$validator->fails()) {
                    $idTaller = $data['idTaller'];
                    $fechaInicio = $data['fechaInicio'];
                    $fechaFinal = $data['fechaFinal'];
                    $horaInicio = $data['horaInicio'];
                    $horaFinal = $data['horaFinal'];
                    $costo = $data['costo'];
                    $ubicacion = $data['ubicacion'];
                    $modalidad = $data['modalidad'];

                    DB::statement(
                        'EXEC paInsertarOfertas ?, ?, ?, ?, ?, ?, ? , ?',
                        [$idTaller, $fechaInicio, $fechaFinal, $horaInicio, $horaFinal, $costo, $ubicacion, $modalidad]
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

    public function destroy(Request $request, $id)
    {
        if (isset($id)) {
            DB::statement('EXEC paEliminarOfertas ?', [$id]);
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

        return response()->json($response, $response['status']);
    }

    public function update(Request $request, $id)
    {
        $data_input = $request->input('data', null);
        $data_input = json_decode($data_input, true);

        if (!$data_input) {
            return response()->json([
                'status' => 400,
                'message' => 'No se encontró el objeto data. No hay datos que modificar'
            ], 400);
        }

        $rules = [
            'idTaller' => 'required|integer',
            'fechaInicio' => 'required|date',
            'fechaFinal' => 'required|date|after_or_equal:fechaInicio',
            'horaInicio' => 'required|time',
            'horaFinal' => 'required|time|after_or_equal:horaInicio',
            'ubicacion' => 'required|string|max:255',
            'modalidad' => 'required|string|max:20',
        ];

        $validator = \Validator::make($data_input, $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 406,
                'message' => 'Datos inválidos',
                'error' => $validator->errors()
            ], 406);
        }

        $oferta = DB::table('ofertas')->find($id);
        if (!$oferta) {
            return response()->json([
                'status' => 404,
                'message' => 'Oferta no encontrada'
            ], 404);
        }

        DB::statement('EXEC paActualizarOfertas ?, ?, ?, ?, ?, ?, ?, ?', [
            $id,
            $data_input['idTaller'],
            $data_input['fechaInicio'],
            $data_input['fechaFinal'],
            $data_input['horaInicio'],
            $data_input['horaFinal'],
            $data_input['ubicacion'],
            $data_input['modalidad']
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Oferta actualizada exitosamente'
        ], 200);
    }
}
