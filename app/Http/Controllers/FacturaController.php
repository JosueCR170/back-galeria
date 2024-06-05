<?php

namespace App\Http\Controllers;

use App\Helpers\JwtAuth;
use App\Models\Factura;
use App\Models\Obra;
use App\Models\User;
use Illuminate\Http\Request;

class FacturaController
{
    //
    public function index(Request $request)
    {
        $jwt = new JwtAuth();
        $decodedToken = $jwt->checkToken($request->header('bearertoken'), true);
        $artistaVerified = isset($decodedToken->nombreArtista) ? $decodedToken->nombreArtista : null;
        if (!$artistaVerified && $jwt->checkToken($request->header('bearertoken'), true)->tipoUsuario) {
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

    public function indexByUserId(Request $request, $id)
    {
        $jwt = new JwtAuth();
        $decodedToken = $jwt->checkToken($request->header('bearertoken'), true);
        $artistaVerified = isset($decodedToken->nombreArtista) ? $decodedToken->nombreArtista : null;

        if ($artistaVerified) {
            $response = array(
                "status" => 200,
                "message" => "No tienes acceso al recurso"
            );
        } else {
            if (!$decodedToken->tipoUsuario) {
                $id = $decodedToken->iss;
            }

            $data = Factura::where('idUsuario', $id)->get();
            $response = array(
                "status" => 200,
                "message" => "Todos los registros de facturas",
                "data" => $data
            );
        }
        return response()->json($response, 200);
    }

    public function indexByArtistId($id)
    {
            $obras = Obra::where('idArtista', $id)->get();

            $facturas = [];

            // Iterar sobre cada obra y obtener sus facturas
            foreach ($obras as $obra) {
                $facturasObra = Factura::where('idObra', $obra->id)->get();
                foreach ($facturasObra as $factura) {
                    $facturas[] = $factura;
                }
            }
            $response = array(
                "status" => 200,
                "message" => "Todos los registros de facturas del artista",
                "data" => $facturas
            );
        
        return response()->json($response, 200);
    }

    public function show(Request $request, $id)
    {
        $jwt = new JwtAuth();
        $decodedToken = $jwt->checkToken($request->header('bearertoken'), true);
        $UserVerified = isset($decodedToken->tipoUsuario) ? $decodedToken->tipoUsuario : null;
        if ($UserVerified) {
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
        } else {
            $response = array(
                'status' => 406,
                'message' => 'No tienes permiso de administrador'
            );
        }
        return response()->json($response, $response['status']);
    }

    public function showWithDate(Request $request)
    {
        $jwt = new JwtAuth();
        $decodedToken = $jwt->checkToken($request->header('bearertoken'), true);
        $artistaVerified = isset($decodedToken->nombreArtista) ? $decodedToken->nombreArtista : null;
        if ($artistaVerified) {
            $response = array(
                "status" => 200,
                "message" => "No tienes acceso al recurso"
            );
        } else {
            $data_input = $request->input('data', null);
            if ($data_input) {
                $data = json_decode($data_input, true);
                $data = array_map('trim', $data);
                $rules = [
                    'fecha' => 'required|date',
                ];
                $isValid = \validator($data, $rules);
                if (!$isValid->fails()) {
                    if ($decodedToken->tipoUsuario) {
                        $idUsuario = $data['idUsuario'];
                    } else {
                        $idUsuario = $decodedToken->iss;
                    }
                    $data = Factura::where('idUsuario', $idUsuario)->where('fecha', $data['fecha'])->get();
                    if (!$data->isEmpty()) {
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
                    } else {
                        $response = array(
                            'status' => 404,
                            'message' => 'No existen facturas registradas en esa fecha'
                        );
                    }
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
        }
        return response()->json($response, $response['status']);
    }

    public function store(Request $request)
    {
        $jwt = new JwtAuth();
        $decodedToken = $jwt->checkToken($request->header('bearertoken'), true);
        $artistaVerified = isset($decodedToken->nombreArtista) ? $decodedToken->nombreArtista : null;

        if ($artistaVerified) {
            $response = array(
                'status' => 406,
                'menssage' => 'Error al crear la factura'
            );
        } else {
            $data_input = $request->input('data', null);
            if ($data_input) {
                $data = json_decode($data_input, true);
                $data = array_map('trim', $data);
                $rules = [
                    'idObra' => 'required|exists:obras,id',
                    'fecha' => 'required|date',
                    'descuento' => 'required',
                ];
                $isValid = \validator($data, $rules);
                if (!$isValid->fails()) {
                    $factura = new Factura();
                    $obra = Obra::find($data['idObra']);
                    $factura->idObra = $data['idObra'];
                    $factura->fecha = $data['fecha'];
                    $factura->subtotal = $obra->precio;
                    $factura->descuento = $data['descuento'];
                    $factura->total = $factura->subtotal - ($factura->subtotal * $factura->descuento);
                    if ($jwt->checkToken($request->header('bearertoken'), true)->tipoUsuario) {
                        $idUsuario = $data['idUsuario'];
                        if (!isset($idUsuario)) {
                            $response = array(
                                'status' => 400,
                                'message' => 'No se encontró el id del usuario'
                            );
                            return response()->json($response, $response['status']);
                        }
                    } else {
                        $idUsuario = $jwt->checkToken($request->header('bearertoken'), true)->iss;
                    }
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
