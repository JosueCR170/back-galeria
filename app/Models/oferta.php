<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Oferta extends Model
{
    use HasFactory;
    
    protected $table = "ofertas";

    public function taller(){
        return $this->belongsTo(Taller::class, 'id');
    }
}
