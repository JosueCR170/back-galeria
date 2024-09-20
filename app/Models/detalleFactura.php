<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class detalleFactura extends Model
{
    use HasFactory;

    protected $table = "detalles_factura";

    public function factura(){
        return $this->belongsTo(Factura::class, 'id');
    }

    public function obra(){
        return $this->belongsTo(Obra::class, 'id');
    }
}
