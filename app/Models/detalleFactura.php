<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleFactura extends Model
{
    use HasFactory;

    protected $table = "detallesfactura";
    public $timestamps = false;


    // public function factura(){
    //     return $this->belongsTo(Factura::class, 'id');
    // }

    // public function obra(){
    //     return $this->belongsTo(Obra::class, 'id');
    // }
}
