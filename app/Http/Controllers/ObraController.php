<?php

namespace App\Http\Controllers;

use App\Models\Obra;
use App\Models\Envio;
use App\Models\DetalleFactura;
use Illuminate\Http\Request;
use App\Helpers\JwtAuth;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ObraController
{
    //

    public function index()
    {
        $data = Obra::all();
        $data = $data->load('artista');

        $response = array(
            "status" => 200,
            "message" => "Todos los registros de las obras",
            "data" => $data
        );
        return response()->json($response, 200);
    }

    public function getTecnica()
    {
        $data = Obra::getTecnica();
        $response = array(
            "status" => 200,
            "message" => "Todas las tecnicas de las obras",
            "data" => $data
        );
        return response()->json($response, 200);
    }

    public function getCategoria()
    {
        $data = Obra::getCategoria();
        $response = array(
            "status" => 200,
            "message" => "Todas las categorias de las obras",
            "data" => $data
        );
        return response()->json($response, 200);
    }

    public function uploadImage(Request $request)
    {
        $image = $request->file('file');
        $filename = \Str::uuid() . "." . $image->getClientOriginalExtension();
        \Storage::disk('obras')->put($filename, \File::get($image));
        $response = array(
            'status' => 201,
            'message' => 'Imagen Guardada',
            'filename' => $filename,
        );
        return response()->json($response, $response['status']);
    }

    public function updateImage(Request $request, string $filename)
    {
        $image = $request->file('file');
        \Storage::disk('obras')->put($filename, \File::get($image));
        $response = array(
            'status' => 201,
            'message' => 'Imagen actualizada',
            'filename' => $filename,
        );
        return response()->json($response, $response['status']);
    }

    public function getImage($filename)
    {
        if (isset($filename)) {
            $exist = \Storage::disk('obras')->exists($filename);
            if ($exist) {
                $file = \Storage::disk('obras')->get($filename);
                return new Response($file, 200);
            } else {
                $response = array(
                    'status' => 404,
                    'message' => 'Imagen no existe'
                );
            }
        } else {
            $response = array(
                'status' => 406,
                'message' => 'No se definió el nombre de la imagen'
            );
        }
        return response()->json($response, $response['status']);
    }
    public function store(Request $request)
{
    $jwt = new JwtAuth();
    $decodedToken = $jwt->checkToken($request->header('bearertoken'), true);
    $adminVerified = isset($decodedToken->tipoUsuario) ? $decodedToken->tipoUsuario : null;
    $artistaVerified = isset($decodedToken->nombreArtista) ? $decodedToken->nombreArtista : null;

    if ($adminVerified !== null || $artistaVerified !== null) {
        $data_input = $request->input('data', null);

        if ($data_input) {
            $data = json_decode($data_input, true);
            $data = array_map('trim', $data);

            $tecnica = Obra::getTecnica();
            $categoria = Obra::getCategoria();
            $rules = [
                'idArtista' => 'required',
                'tecnica' => ['required', Rule::in($tecnica)],
                'nombre' => 'required|string',
                'tamano' => 'required|string',
                'precio' => 'required|decimal:0,2',
                'disponibilidad' => 'required|boolean',
                'categoria' => ['required', Rule::in($categoria)],
                'imagen' => 'required|string',
                'fechaCreacion' => 'required|date',
                'fechaRegistro' => 'required|date'
            ];

            $isValid = \validator($data, $rules);
            if (!$isValid->fails()) {
                try {
                    DB::beginTransaction(); // Inicia la transacción

                    $obra = new Obra();
                    $obra->idArtista = $data['idArtista'];
                    $obra->tecnica = $data['tecnica'];
                    $obra->nombre = $data['nombre'];
                    $obra->tamano = $data['tamano'];
                    $obra->precio = $data['precio'];
                    $obra->disponibilidad = $data['disponibilidad'];
                    $obra->categoria = $data['categoria'];
                    $obra->imagen = $data['imagen'];
                    $obra->fechaCreacion = $data['fechaCreacion'];
                    $obra->fechaRegistro = $data['fechaRegistro'];
                    $obra->save();

                    $obra = Obra::find($obra->id);

                    DB::commit();

                    $response = [
                        'status' => 201,
                        'message' => 'Obra guardada exitosamente',
                        'obra' => $obra
                    ];
                } catch (\Exception $e) {
                    DB::rollBack();
                    $response = [
                        'status' => 500,
                        'message' => 'Error al guardar la obra',
                        'error' => $e->getMessage(),
                    ];
                }
            } else {
                $response = [
                    'status' => 406,
                    'message' => 'Error: verifica rellenar todos los datos',
                    'error' => $isValid->errors(),
                ];
            }
        } else {
            $response = [
                'status' => 400,
                'message' => 'No se encontraron todos los datos necesarios'
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


    public function destroy(Request $request, $id)
    {
        $jwt = new JwtAuth();
        $decodedToken = $jwt->checkToken($request->header('bearertoken'), true);
        $adminVerified = isset($decodedToken->tipoUsuario) ? $decodedToken->tipoUsuario : null;
        $artistaVerified = isset($decodedToken->nombreArtista) ? $decodedToken->nombreArtista : null;

        if ($adminVerified !== null || $artistaVerified !== null) {

            if (isset($id)) {
                $delete = Obra::where('id', $id)->delete();
                if ($delete) {
                    $response = array(
                        'status' => 200,
                        'menssage' => 'Obra eliminada',
                    );
                } else {
                    $response = array(
                        'status' => 400,
                        'menssage' => 'No se pudo eliminar la Obra, compruebe que exista'
                    );
                }
            } else {
                $response = array(
                    'status' => 406,
                    'menssage' => 'Falta el identificador del recurso a eliminar'
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

    public function destroyImage($filename){
        if (\Storage::disk('obras')->exists($filename)){
            \Storage::disk('obras')->delete($filename);
            $response = array(
                'status' => 200,
                'menssage' => 'Imagen eliminada'
            );
        } else {
            $response = array(
                'status' => 404,
                'menssage' => 'Imagen no encontrada'
            );
        }
        return response()->json($response, $response['status']);
    }

    public function update(Request $request, $id)
    {
        $jwt = new JwtAuth();
        $decodedToken = $jwt->checkToken($request->header('bearertoken'), true);
        $adminVerified = isset($decodedToken->tipoUsuario) ? $decodedToken->tipoUsuario : null;
        $artistaVerified = isset($decodedToken->nombreArtista) ? $decodedToken->nombreArtista : null;

        if ($adminVerified !== null || $artistaVerified !== null) {
            $obra = Obra::find($id);
            if (!$obra) {
                $response = [
                    'status' => 404,
                    'message' => 'Obra no encontrada'
                ];
                return response()->json($response, $response['status']);
            }

            $data_input = $request->input('data', null);

            if (!$data_input) {
                $response = [
                    'status' => 400,
                    'message' => 'No se proporcionaron datos para actualizar'
                ];
                return response()->json($response, $response['status']);
            }

            if ($data_input) {
                $data = json_decode($data_input, true);
                $tecnica = Obra::getTecnica();
                $categoria = Obra::getCategoria();
                $isValid = \Validator::make($data, [

                    'tecnica' => Rule::in($tecnica),
                    'nombre' => 'string',
                    'tamano' => 'string',
                    'precio' => 'decimal:0,2',
                    'categoria' => Rule::in($categoria),
                    'fechaCreacion' => 'date',
                    'fechaRegistro' => 'date'
                ]);

                if ($isValid->fails()) {
                    $response = [
                        'status' => 406,
                        'message' => 'Datos inválidos',
                        'errors' => $isValid->errors()
                    ];
                    return response()->json($response, $response['status']);
                }


                $obra->tecnica = isset($data['tecnica']) ? $data['tecnica'] : $obra->tecnica;
                $obra->nombre = isset($data['nombre']) ? $data['nombre'] : $obra->nombre;
                $obra->tamano = isset($data['tamano']) ? $data['tamano'] : $obra->tamano;
                $obra->precio = isset($data['precio']) ? $data['precio'] : $obra->precio;
                $obra->categoria = isset($data['categoria']) ? $data['categoria'] : $obra->categoria;
                $obra->fechaCreacion = isset($data['fechaCreacion']) ? $data['fechaCreacion'] : $obra->fechaCreacion;
                $obra->fechaRegistro = isset($data['fechaRegistro']) ? $data['fechaRegistro'] : $obra->fechaRegistro;
            }

            $obra->save();

            $response = [
                'status' => 200,
                'message' => 'Obra actualizada',
                'obra' => $obra
            ];
        }
        return response()->json($response, $response['status']);
    }

    public function updateDisponibilidad(Request $request, $id)
    {
        $jwt = new JwtAuth();
        $decodedToken = $jwt->checkToken($request->header('bearertoken'), true);
        $adminVerified = isset($decodedToken->tipoUsuario) ? $decodedToken->tipoUsuario : null;
        $artistaVerified = isset($decodedToken->nombreArtista) ? $decodedToken->nombreArtista : null;
        if ($adminVerified !== null || $artistaVerified !== null) {
            $obra = Obra::find($id);
            if (!$obra) {
                $response = [
                    'status' => 404,
                    'message' => 'Obra no encontrada'
                ];
                return response()->json($response, $response['status']);
            }

            $data_input = $request->input('data', null);
            if (!$data_input) {
                $response = [
                    'status' => 400,
                    'message' => 'No se proporcionaron datos para actualizar'
                ];
                return response()->json($response, $response['status']);
            }
            if ($data_input) {
                $data = json_decode($data_input, true);
                // $data = array_map('trim', $data);
                $isValid = \Validator::make($data, [

                    'disponibilidad' => 'boolean',
                ]);

                if ($isValid->fails()) {
                    $response = [
                        'status' => 406,
                        'message' => 'Datos inválidos',
                        'errors' => $isValid->errors()
                    ];
                    return response()->json($response, $response['status']);
                }
                $obra->disponibilidad = isset($data['disponibilidad']) ? $data['disponibilidad'] : $obra->disponibilidad;
            }
            $obra->save();
            $response = [
                'status' => 200,
                'message' => 'Disponibilidad actualizada',
                'obra' => $obra
            ];
        }
        return response()->json($response, $response['status']);
    }

    public function show($id)
    {
        $data = Obra::find($id);
        if (is_object($data)) {
            $data = $data->load('artista');
            $response = array(
                'status' => 200,
                'menssage' => 'obra encontrada',
                'obra' => $data
            );
        } else {
            $response = array(
                'status' => 404,
                'menssage' => 'Recurso no encontrado'
            );

        }
        return response()->json($response, $response['status']);

    }

    public function indexByArtistId($id)
    {
    $data = Obra::where('idArtista', $id)
        ->with('artista', 'detallesFactura.factura')
        ->get();

    $response = array(
        "status" => 200,
        "message" => "Todas las obras del artista con sus facturas",
        "data" => $data
    );

    return response()->json($response, 200);
    }

    public function indexByEnvioId($id)
    {
    $envios=Envio::Where('id',$id)->pluck('idFactura');
    $detalles = DetalleFactura::whereIn('idFactura', $envios)->pluck('idObra');
    $obras = Obra::whereIn('id', $detalles)->distinct()->get();
    $response = array(
        "status" => 200,
        "message" => "Todas las obras del envio",
        "data" => $obras
    );

    return response()->json($response, 200);
    }

}
