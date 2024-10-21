<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\JwtAuth;
use Illuminate\Support\Facades\DB;

class AdministradorDBController
{
public function restoreBD(Request $request)
{
    $jwt = new JwtAuth();
    if (!$jwt->checkToken($request->header('bearertoken'), true)->tipoUsuario) {
        return response()->json([
            'status' => 406,
            'message' => 'No tienes permiso de administrador'
        ], 406);
    }

    $data_input = $request->input('data', null);
    if (!$data_input) {
        return response()->json([
            'status' => 400,
            'message' => 'No se encontraron todos los datos necesarios'
        ], 400);
    }

    $data = json_decode($data_input, true);
    $data = array_map('trim', $data);

    $rules = [
        'backupPath' => 'required|string',
        'fileName' => 'required|string',
    ];

    $validator = \Validator::make($data, $rules);
    if ($validator->fails()) {
        return response()->json([
            'status' => 406,
            'message' => 'Error: verifica rellenar todos los datos',
            'errors' => $validator->errors(),
        ], 406);
    }

    $backupPath = $data['backupPath'];
    $fileName = $data['fileName'];


    $result = DB::connection('master')->statement('EXEC paRestoreGaleriaDB @backupPath = ?, @fileName = ?', [$backupPath, $fileName]);
 
    if ($result) {
        return response()->json([
            'status' => 200,
            'message' => 'Restauración completada exitosamente.'
        ], 200);
    } else {
        return response()->json([
            'status' => 404,
            'message' => 'Error durante la restauración. Recurso no encontrado.'
        ], 404);
    }
}

// public function backupBD(Request $request)
// {
//     $jwt = new JwtAuth();

//     if (!$jwt->checkToken($request->header('bearertoken'), true)->tipoUsuario) {
//         return response()->json([
//             'status' => 406,
//             'message' => 'No tienes permiso de administrador'
//         ], 406);
//     }
//     $backupPath = 'C:\SQLBackups';
//     $backupFileName = 'galeria_db_Backup_' . now()->format('Ymd_Hi') . '.bak';
//     $fullPath = $backupPath . '\\' . $backupFileName;
    
//     if (!file_exists($backupPath)) {
//         mkdir($backupPath, 0777, true); 
//     }

//     DB::statement('EXEC paBackupGaleriaDB @backupPath = ?',[$backupPath]);

//     if (file_exists($fullPath)) {
//         return response()->download($fullPath);  
//     } else {
//         return response()->json([
//             'status' => 404,
//             'message' => 'Error al generar el backup',
//             'nombre de archivo'=>$fullPath
//         ], 404);
//     }
// }

public function backupBD(Request $request)
{
    $jwt = new JwtAuth();

    if (!$jwt->checkToken($request->header('bearertoken'), true)->tipoUsuario) {
        return response()->json([
            'status' => 406,
            'message' => 'No tienes permiso de administrador'
        ], 406);
    }

    $backupPath = 'C:\SQLBackups';
    $backupFileName = 'galeria_db_Backup.bak';
    $fullPath = $backupPath . '\\' . $backupFileName;
    if (!file_exists($backupPath)) {
        mkdir($backupPath, 0777, true); 
    }
    DB::statement('EXEC paBackupGaleriaDB @backupPath = ?', [$backupPath]);

    if (file_exists($fullPath)) {
        return response()->download($fullPath);  
    } else {
        return response()->json([
            'status' => 404,
            'message' => 'Error al generar el backup',
            'nombre de archivo' => $fullPath
        ], 404);
    }
}

}
