<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    use HasFactory;

    protected $table = "factura";

    public function usuario(){
        return $this->belongsTo(User::class, 'id');
    }

    public function obra(){
    return $this->belongsTo(Obra::class, 'idObra');       
    }
}
