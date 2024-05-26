<?php

namespace App\Http\Controllers;

use App\Helpers\JwtAuth;
use App\Models\Envio;
use App\Models\Factura;
use App\Models\Obra;
use Illuminate\Http\Request;

class EnvioController
{
    //
    public function index(Request $request)
    {
        $jwt = new JwtAuth();
        if ($jwt->checkToken($request->header('bearertoken'), true)->tipoUsuario) {
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

    public function indexByUserId(Request $request)
    {
        $jwt = new JwtAuth();
        $idUsuario = $jwt->checkToken($request->header('bearertoken'), true)->idUsuario;
        $facturasIds = Factura::where('idUsuario', $idUsuario)->pluck('id');

        if ($facturasIds->isEmpty()) {
            $response = array(
                "status" => 404,
                "message" => "No se encontraron facturas para el usuario",
            );
            return response()->json($response, 404);
        }
        $data = Envio::whereIn('idFactura', $facturasIds)->get();
        $response = array(
            "status" => 200,
            "message" => "Todos los registros de envios del usuario",
            "data" => $data
        );
        return response()->json($response, 200);
    }

    public function store(Request $request)
    {

        $data_input = $request->input('data', null);
        if ($data_input) {
            $data = json_decode($data_input, true);
            $data = array_map('trim', $data);
            $rules = [
                'estado' => 'required|string|max:255',
                'fechaEnviado' => 'required|date',
                'fechaRecibido' => 'date',
                'idFactura' => 'required',
            ];
            $isValid = \validator($data, $rules);
            if (!$isValid->fails()) {
                $Envio = new Envio();
                $Envio->estado = $data['estado'];
                $Envio->fechaEnviado = $data['fechaEnviado'];
                $Envio->fechaRecibido = $data['fechaRecibido'];
                $Envio->idFactura = $data['idFactura'];

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

    public function update(Request $request, $id)
    {
        $authArtista = false;

        $jwt = new JwtAuth();
        $authUserAdmin = $jwt->checkToken($request->header('bearertoken'), true)->tipoUsuario;
        if ($authUserAdmin === null) {
            $idArtista = $jwt->checkToken($request->header('bearertoken'), true)->id;
            $obra = Obra::find($request['idFactura']->idObra);
            if ($obra->idArtista === $idArtista){
                $authArtista = true;
            }
        }

        if ($authUserAdmin || $authArtista) {
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
                'fechaEnviado' => 'date',
                'fechaRecibido' => 'date',
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

    public function destroy(Request $request, $id)
    {
        $jwt = new JwtAuth();
        if ($jwt->checkToken($request->header('bearertoken'), true)->tipoUsuario) {
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
