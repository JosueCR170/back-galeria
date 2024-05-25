<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Oferta extends Model
{
    use HasFactory;
    
    protected $table = "oferta";

    public function artista(){
        return $this->belongsTo(Artista::class, 'idArtista');
    }

    public function taller(){
        return $this->belongsTo(Taller::class, 'idTaller');
    }
}
