<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use App\Models\User;

class JwtAuthUser{
    private $key;
    function __construct(){
        $this->key="aswqdfewqeddafe23ewresa";
    }

    public function getToken($nombreUsuario, $password){
        $user=User::where(['nombreUsuario'=>$nombreUsuario, 'password'=> hash('sha256',$password)])->first();
        if(is_object($user)){
            $token=array(
                'iss'=>$user->id,
                'nombre'=>$user->nombre,
                'telefono'=>$user->telefono,
                'correo'=>$user->correo,
                'tipoUsuario'=>$user->tipoUsuario,
                'usuario'=>$user->usuario,
                'iat'=>time(),
                'exp'=>time()+(10000)
            );
            $data=JWT::encode($token,$this->key,'HS256');
        }else{
            $data=array(
                'status'=>401,
                'message'=>'Datos de autenticación incorrectos'
            );
        }
        return $data;
    }
    public function checkToken($jwt,$getId=false){
        $authFlag=false;
        if(isset($jwt)){
            try{
                $decoded=JWT::decode($jwt,new Key($this->key,'HS256'));
            }catch(\DomainException $ex){
                $authFlag=false;
            }catch(ExpiredException $ex){
                $authFlag=false;
            }
            if(!empty($decoded)&&is_object($decoded)&&isset($decoded->iss)){
                $authFlag=true;
            }
            if($getId && $authFlag){
                return $decoded;
            }
        }
        return $authFlag;
    }
}