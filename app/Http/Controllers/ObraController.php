<?php

namespace App\Http\Controllers;

use App\Models\Obra;
use Illuminate\Http\Request;
use App\Helpers\JwtAuthArtista;

class ObraController
{
    //

    public function index()
    {
        $data = Obra::all();
        $response = array(
            "status" => 200,
            "message" => "Todos los registros de las obras",
            "data" => $data
        );
        return response()->json($response, 200);
    }


    public function store(Request $request)
    {
        $jwt = new JwtAuthArtista();
        if (!$jwt->checkToken($request->header('bearertoken'), true)->permisoAdmin) {
            $response = array(
                'status' => 406,
                'menssage' => 'No tienes permiso de administrador'
            );
        } else {

            $data_input = $request->input('data', null);
            $file = $request->file('file');

            if ($data_input && $file) {
                $data = json_decode($data_input, true);
                $data = array_map('trim', $data);

                $isValid = \Validator::make($data, [
                    'idPelicula' => 'required|exists:peliculas,id',
                    'descripcion' => 'required',
                ]);

                if (!$isValid->fails()) {
                    $imagen = new Imagen();
                    $filename = \Str::uuid() . "." . $file->getClientOriginalExtension();

                    \Storage::disk('peliculas')->put($filename, \File::get($file));

                    $imagen->idPelicula = $data['idPelicula'];
                    $imagen->descripcion = $data['descripcion'];
                    $imagen->imagen = $filename;
                    $imagen->save();

                    $response = [
                        'status' => 201,
                        'message' => 'Imagen guardada',
                        'filename' => $filename
                    ];
                } else {
                    $response = [
                        'status' => 406,
                        'message' => 'Error: verifica rellenar todos los datos',
                        'error' => $isValid->errors()
                    ];
                }
            } else {
                $response = [
                    'status' => 400,
                    'message' => 'No se encontraron todos los datos necesarios'
                ];
            }
        }
        return response()->json($response, $response['status']);
    }

    public function destroy(Request $request, $id)
    {

        $jwt = new JwtAuth();
        if (!$jwt->checkToken($request->header('bearertoken'), true)->permisoAdmin) {
            $response = array(
                'status' => 406,
                'menssage' => 'No tienes permiso de administrador'

            );
           
        } else {

        if (isset($id)) {
            $imagen = Imagen::find($id);
            $delete = Imagen::where('id', $id)->delete();
            if ($delete) {

                $filename = $imagen->imagen;
                \Storage::disk('peliculas')->delete($filename);
                $response = array(
                    'status' => 200,
                    'menssage' => 'Imagen eliminada',
                );
            } else {
                $response = array(
                    'status' => 400,
                    'menssage' => 'No se pudo eliminar la Imagen, compruebe que exista'
                );
            }
        } else {
            $response = array(
                'status' => 406,
                'menssage' => 'Falta el identificador del recurso a eliminar'
            );
        }
    }
        return response()->json($response, $response['status']);
    }

    public function update(Request $request, $id)
    {
        $jwt = new JwtAuth();
        if (!$jwt->checkToken($request->header('bearertoken'), true)->permisoAdmin) {
            $response = array(
                'status' => 406,
                'menssage' => 'No tienes permiso de administrador'

            );
           
        } else {
        $imagen = Imagen::find($id);
        if (!$imagen) {
            $response = [
                'status' => 404,
                'message' => 'Imagen no encontrada'
            ];
            return response()->json($response, $response['status']);
        }

        $data_input = $request->input('data', null);
        $file = $request->file('file');

        if (!$data_input && !$file) {
            $response = [
                'status' => 400,
                'message' => 'No se proporcionaron datos ni archivo para actualizar'
            ];
            return response()->json($response, $response['status']);
        }
        if ($data_input) {
            $data = json_decode($data_input, true);
            $data = array_map('trim', $data);
            $isValid = \Validator::make($data, [
                'idPelicula' => 'exists:peliculas,id'
            ]);
            if ($isValid->fails()) {
                $response = [
                    'status' => 406,
                    'message' => 'Datos inválidos',
                    'errors' => $isValid->errors()
                ];
                return response()->json($response, $response['status']);
            }
            $imagen->idPelicula = isset($data['idPelicula']) ? $data['idPelicula'] : $imagen->idPelicula;
            $imagen->descripcion = isset($data['descripcion']) ? $data['descripcion'] : $imagen->descripcion;
        }

        if ($file) {
            \Storage::disk('peliculas')->put($imagen->imagen, \File::get($file));
        }
        $imagen->save();

        $response = [
            'status' => 200,
            'message' => 'Imagen actualizada',
            'imagen' => $imagen
        ];
    }
        return response()->json($response, $response['status']);
    }

    public function show($id)
    {
            $data=Obra::find($id);
            if(is_object($data)){
                $data=$data->load('artista');
                $response=array(
                'status'=>200,
                'menssage'=>'obra encontrada',
                'obra'=>$data
                );
            }
            else{
                $response = array(
                    'status'=>404,
                    'menssage'=>'Recurso no encontrado'
                );

            }
            return response()->json($response,$response['status']);

    }

}
