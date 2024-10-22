<?php

namespace App\Http\Controllers;

use App\Helpers\JwtAuth;
use App\Models\DetalleFactura;
use App\Models\Envio;
use App\Models\Factura;
use App\Models\Obra;
use Illuminate\Http\Request;

class EnvioController
{
    //
    public function index(Request $request) //listo
    {
        $jwt = new JwtAuth();
        $decodedToken = $jwt->checkToken($request->header('bearertoken'), true);
        $adminVerified = isset($decodedToken->tipoUsuario) ? $decodedToken->tipoUsuario : null;
        $artistaVerified = isset($decodedToken->nombreArtista) ? $decodedToken->nombreArtista : null;

        if ($adminVerified && $artistaVerified == null) {
            $data = Envio::all();
            $response = array(
                "status" => 200,
                "message" => "Todos los registros de envios",
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

    public function indexByArtist(Request $request) //Por discutir
    {
        $jwt = new JwtAuth();
        $decodedToken = $jwt->checkToken($request->header('bearertoken'), true);
        $artistaVerified = isset($decodedToken->nombreArtista) ? $decodedToken->nombreArtista : null;
        $data=[];
        if ($artistaVerified) {

             $obras = Obra::where('idArtista', $decodedToken->iss)->pluck('id');
             $detalles = DetalleFactura::whereIn('idObra', $obras)->pluck('idFactura');

             $facturas = Factura::whereIn('id', $detalles)->pluck('id');
             $data = Envio::whereIn('idFactura', $facturas)->get();

         }

        // if (Empty($data)) {
        //     $response = array(
        //         "status" => 404,
        //         "message" => "No se encontraron envios",
        //     );
        //     return response()->json($response, 404);
        // }

        $response = array(
            "status" => 200,
            "message" => "Todos los registros de envios",
            "data" => $data
        );
        return response()->json($response, 200);
    }

    public function indexByUser($id) //Por discutir
    {
        $facturas = Factura::where('idUsuario', $id)->pluck('id');
        $data = Envio::whereIn('idFactura', $facturas)->get();
        if (!$data) {
            $response = array(
                "status" => 404,
                "message" => "No se encontraron envios",
            );
            return response()->json($response, 404);
        }
        $response = array(
            "status" => 200,
            "message" => "Todos los registros de envios",
            "data" => $data
        );
        return response()->json($response, 200);
    }

    public function indexByUserAdmin(Request $request, $aux, $id) //Por discutir
    {
        $jwt = new JwtAuth();
        $decodedToken = $jwt->checkToken($request->header('bearertoken'), true);
        $adminVerified = isset($decodedToken->tipoUsuario) ? $decodedToken->tipoUsuario : null;
        $artistaVerified = isset($decodedToken->nombreArtista) ? $decodedToken->nombreArtista : null;
        if ($adminVerified && $artistaVerified == null) {
            if ($aux == 1) {
            $obras = Obra::where('idArtista', $id)->pluck('id');
            $facturas = Factura::whereIn('idObra', $obras)->pluck('id');
            $data = Envio::whereIn('idFactura', $facturas);
            } else {
                $facturas = Factura::where('idUsuario', $id)->pluck('id');
            $data = Envio::whereIn('idFactura', $facturas);
            }

            if (!$data) {
                $response = array(
                    "status" => 404,
                    "message" => "No se encontraron envios",
                );
                return response()->json($response, 404);
            }

            $response = array(
                "status" => 200,
                "message" => "Todos los registros de envios",
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
            $data = Factura::find($id);
            if (is_object($data)) {
                $response = array(
                    'status' => 200,
                    'message' => 'Datos del envio',
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

    public function store(Request $request) //Listo
    {

        $data_input = $request->input('data', null);
        if ($data_input) {
            $data = json_decode($data_input, true);
            $data = array_map('trim', $data);
            $rules = [
                'estado' => 'required|string|max:255',
                'idFactura' => 'required|exists:facturas,id',
                'direccion'=>'required|string',
                'codigoPostal'=>'required',
                'provincia'=>'required|string',
                'ciudad'=>'required|string',
                'fechaEnviado'=>'date | nullable',
                'fechaRecibido'=>'date | null'
            ];
            $isValid = \validator($data, $rules);
            if (!$isValid->fails()) {
                $Envio = new Envio();
                $Envio->estado = $data['estado'];
                $Envio->idFactura = $data['idFactura'];
                $Envio->direccion = $data['direccion'];
                $Envio->codigoPostal = $data['codigoPostal'];
                $Envio->provincia = $data['provincia'];
                $Envio->ciudad = $data['ciudad'];

                if (isset($data_input['fechaEnviado'])) {
                    $Envio->fechaEnviado = $data_input['fechaEnviado'];
                }
                if (isset($data_input['fechaRecibido'])) {
                    $Envio->fechaRecibido = $data_input['fechaRecibido'];
                }

                $Envio->save();
                $response = array(
                    'status' => 201,
                    'message' => 'Envio guardado',
                    'Envio' => $Envio
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

    public function update(Request $request, $id) //Listo
    {
        $jwt = new JwtAuth();
        $decodedToken = $jwt->checkToken($request->header('bearertoken'), true);
        $adminVerified = isset($decodedToken->tipoUsuario) ? $decodedToken->tipoUsuario : null;
        $artistaVerified = isset($decodedToken->nombreArtista) ? $decodedToken->nombreArtista : null;

        if ($adminVerified || $artistaVerified) {
            $envio = Envio::find($id);
            if (!$envio) {
                $response = [
                    'status' => 404,
                    'message' => 'Envio no encontrado'
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
                'estado' => 'string|max:255',
                'direccion'=>'string',
                'provincia'=>'string',
                'ciudad'=>'string',
                'fechaEnviado'=>'date|nullable',
                'fechaRecibido'=>'date|nullable'

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

            if (isset($data_input['estado'])) {
                $envio->estado = $data_input['estado'];
            }
            if (isset($data_input['fechaEnviado'])) {
                $envio->fechaEnviado = $data_input['fechaEnviado'];
            }
            if (isset($data_input['fechaRecibido'])) {
                $envio->fechaRecibido = $data_input['fechaRecibido'];
            }

            $envio->save();

            $response = [
                'status' => 201,
                'message' => 'Envio actualizada',
                'Factura' => $envio
            ];
        } else {
            $response = array(
                'status' => 406,
                'menssage' => 'No tienes permiso de administrador'

            );
        }
        return response()->json($response, $response['status']);
    }

    public function destroy(Request $request, $id) //Listo
    {
        $jwt = new JwtAuth();
        $decodedToken = $jwt->checkToken($request->header('bearertoken'), true);
        $adminVerified = isset($decodedToken->tipoUsuario) ? $decodedToken->tipoUsuario : null;
        $artistaVerified = isset($decodedToken->nombreArtista) ? $decodedToken->nombreArtista : null;
        if ($adminVerified && !$artistaVerified) {
            if (isset($id)) {
                $deleted = Envio::where('id', $id)->delete();
                if ($deleted) {
                    $response = array(
                        'status' => 200,
                        'message' => 'Envio liminada',
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
