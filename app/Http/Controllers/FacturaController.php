<?php

namespace App\Http\Controllers;

use App\Helpers\JwtAuthArtista;
use App\Helpers\JwtAuthUser;
use App\Models\Factura;
use Illuminate\Http\Request;

class FacturaController
{
    //
    public function index(Request $request)
    {
        $jwt = new JwtAuthUser();
        if ($jwt->checkToken($request->header('bearertoken'), true)->tipoUsuario) {
        $data = Factura::all();
        $response = array(
            "status" => 200,
            "message" => "Todos los registros de facturas",
            "data" => $data
        );
    } else {
        $response = array(
            'status' => 406,
            'menssage' => 'No tienes permiso de administrador'

        );
    }
        return response()->json($response, 200);
    }

    public function indexById(Request $request)
    {
        $jwt = new JwtAuthUser();
        $idUsuario = $jwt->checkToken($request->header('bearertoken'), true)->idUsuario;
        
        $data = Factura::where('idUsuario', $idUsuario);
        $response = array(
            "status" => 200,
            "message" => "Todos los registros de facturas",
            "data" => $data
        );
        return response()->json($response, 200);
    }

    public function show($id)
    {
        $data = Factura::find($id);
        if (is_object($data)) {
            $response = array(
                'status' => 200,
                'message' => 'Datos de la factura',
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

    public function showWithDate(Request $request, $date)
    {
        $jwtUser = new JwtAuthUser();
        $idUsuario = $jwtUser->checkToken($request->header('bearertoken'), true)->idUsuario;
        
        $data = Factura::where('idUsuario', $idUsuario)->where('date', $date);
        if (is_object($data)) {
            $response = array(
                'status' => 200,
                'message' => 'Datos de las facturas',
                'data' => $data
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
        $jwt = new JwtAuthUser();
        $idUsuario = $jwt->checkToken($request->header('bearertoken'), true)->idUsuario;

        $data_input = $request->input('data', null);
        if ($data_input) {
            $data = json_decode($data_input, true);
            $data = array_map('trim', $data);
            $rules = [
                'fecha' => 'required|date',
                'total' => 'required',
                'subtotal' => 'required',
                'descuento' => 'required',
            ];
            $isValid = \validator($data, $rules);
            if (!$isValid->fails()) {
                $factura = new Factura();
                $factura->fecha = $data['fecha'];
                $factura->total = hash('sha256', $data['total']);
                $factura->subtotal = $data['subtotal'];
                $factura->descuento = $data['descuento'];
                $factura->idUsuario = $idUsuario;

                $factura->save();
                $response = array(
                    'status' => 201,
                    'message' => 'Factura guardada',
                    'Factura' => $factura
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
        $jwt = new JwtAuthUser();
        if ($jwt->checkToken($request->header('bearertoken'), true)->tipoUsuario) {

            $factura = Factura::find($id);
            if (!$factura) {
                $response = [
                    'status' => 404,
                    'message' => 'Factura no encontrado'
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
                'fecha' => 'required|date',
                // 'total'=>'required',
                // 'subtotal'=>'required',
                // 'descuento'=>'required',
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

            if (isset($data_input['fecha'])) { $factura->fecha = $data_input['fecha']; }
            if (isset($data_input['total'])) { $factura->total = $data_input['total']; }
            if (isset($data_input['subtotal'])) { $factura->subtotal = $data_input['subtotal']; }
            if (isset($data_input['descuento'])) { $factura->descuento = $data_input['descuento']; }

            $factura->save();

            $response = [
                'status' => 201,
                'message' => 'Factura actualizada',
                'Factura' => $factura
            ];
        } else {
            $response = array(
                'status' => 406,
                'menssage' => 'No tienes permiso de administrador'

            );
        }
        return response()->json($response, $response['status']);
    }

    public function destroy(Request $request, $id)
    {
        $jwt = new JwtAuthUser();
        if ($jwt->checkToken($request->header('bearertoken'), true)->tipoUsuario) {
            if (isset($id)) {
                $deleted = Factura::where('id', $id)->delete();
                if ($deleted) {
                    $response = array(
                        'status' => 200,
                        'message' => 'Factura eliminada',
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
}
