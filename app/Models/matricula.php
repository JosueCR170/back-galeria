<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Matricula extends Model
{
    use HasFactory;

    protected $table = "matriculas";

    public function usuario(){
        return $this->belongsTo(User::class, 'id');
    }

    public function oferta(){
        return $this->belongsTo(Oferta::class, 'id');
    }
}
