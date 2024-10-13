<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\JwtAuth;
use App\Models\detalleFactura;
use Illuminate\Support\Facades\DB;

class DetalleFacturaController
{
    //
    public function index(Request $request)
    {
        $jwt = new JwtAuth();
        $decodedToken = $jwt->checkToken($request->header('bearertoken'), true);
        $artistaVerified = isset($decodedToken->nombreArtista) ? $decodedToken->nombreArtista : null;
        if (!$artistaVerified && $jwt->checkToken($request->header('bearertoken'), true)->tipoUsuario) {
            $data = detalleFactura::all();
            $data = $data->load('factura');
            $data = $data->load('obra');
            $response = array(
                "status" => 200,
                "message" => "Todos los registros de detalles factura",
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

   

    public function show(Request $request, $id)
    {
        $jwt = new JwtAuth();
        $decodedToken = $jwt->checkToken($request->header('bearertoken'), true);
        $UserVerified = isset($decodedToken->tipoUsuario) ? $decodedToken->tipoUsuario : null;
        if ($UserVerified) {
            $data = detalleFactura::find($id);
            if (is_object($data)) {
                $data = $data->load('factura');
                $data = $data->load('obra');
                $response = array(
                    'status' => 200,
                    'message' => 'Datos de los Detalles Factura',
                    'Artista' => $data
                );
            } else {
                $response = array(
                    'status' => 404,
                    'message' => 'Recurso no encontrado'
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

    
    public function store(Request $request)
    {
        $data_input = $request->input('data', null);
        if ($data_input) {
            $data = json_decode($data_input, true);
            $data = array_map('trim', $data);
            $rules = [
                'idFactura' => 'required|exists:facturas,id',
                'idObra' => 'required|exists:obras,id',
                'subtotal' => 'required|numeric',
            ];
            $isValid = \validator($data, $rules);
            if (!$isValid->fails()) {
                
                $idFactura = $data['idFactura'];
                $idObra = $data['idObra'];
                $subtotal = (float) $data['subtotal'];

                DB::statement(
                    'EXEC paInsertarDetallesFactura ?, ?, ?',
                    [$idFactura, $idObra, $subtotal]
                );
                $response = array(
                    'status' => 201,
                    'message' => 'Detalle Factura guardado'
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

    // public function update(Request $request, $id)
    // {
    //     $jwt = new JwtAuth();
    //     if ($jwt->checkToken($request->header('bearertoken'), true)->tipoUsuario) {

    //         $factura = Factura::find($id);
    //         if (!$factura) {
    //             $response = [
    //                 'status' => 404,
    //                 'message' => 'Factura no encontrado'
    //             ];
    //             return response()->json($response, $response['status']);
    //         }

    //         $data_input = $request->input('data', null);
    //         $data_input = json_decode($data_input, true);

    //         if (!$data_input) {
    //             $response = [
    //                 'status' => 400,
    //                 'message' => 'No se encontró el objeto data. No hay datos que modificar'
    //             ];
    //             return response()->json($response, $response['status']);
    //         }

    //         $rules = [
    //             'fecha' => 'date',
    //         ];

    //         $validator = \validator($data_input, $rules);

    //         if ($validator->fails()) {
    //             $response = [
    //                 'status' => 406,
    //                 'message' => 'Datos inválidos',
    //                 'error' => $validator->errors()
    //             ];
    //             return response()->json($response, $response['status']);
    //         }

    //         if (isset($data_input['fecha'])) {
    //             $factura->fecha = $data_input['fecha'];
    //         }

    //         $factura->save();

    //         $response = [
    //             'status' => 201,
    //             'message' => 'Factura actualizada',
    //             'Factura' => $factura
    //         ];
    //     } else {
    //         $response = array(
    //             'status' => 406,
    //             'menssage' => 'No tienes permiso de administrador'

    //         );
    //     }
    //     return response()->json($response, $response['status']);
    // }

    public function destroy(Request $request, $id)
    {
        $jwt = new JwtAuth();
        $decodedToken = $jwt->checkToken($request->header('bearertoken'), true);
        $artistaVerified = isset($decodedToken->nombreArtista) ? $decodedToken->nombreArtista : null;


        if (!$artistaVerified && $jwt->checkToken($request->header('bearertoken'), true)->tipoUsuario) {
            if (isset($id)) {
                $deleted = detalleFactura::where('id', $id)->delete();
                if ($deleted) {
                    $response = array(
                        'status' => 200,
                        'message' => 'DetalleFactura eliminado',
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
