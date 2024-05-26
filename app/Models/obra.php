<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Obra extends Model
{
    use HasFactory;

    protected $table = "obra";

    protected static $tecnicasArte = [
        'Óleo sobre lienzo',
        'Acuarela',
        'Acuarela sobre papel',
        'Témpera',
        'Pintura al pastel',
        'Pintura al fresco',
        'Pintura digital',
        'Talla en madera',
        'Escultura en mármol',
        'Grabado',
        'Serigrafía',
        'Fotografía artística',
        'Arte digital',
        'Collage',
        'Pirograbado',
        'Escultura en bronce'
    ];

    public static function getTecnica(){return self::$tecnicasArte;}
    
    public function artista(){
        return $this->belongsTo(Artista::class, 'id');
    }
}
