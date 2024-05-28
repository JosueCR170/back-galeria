<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Obra extends Model
{
    use HasFactory;

    protected $table = "obras";

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

    protected static $categorias = [
        'Cubismo',
        'Impresionismo',
        'Expresionismo',
        'Realismo',
        'Surrealismo',
        'Abstracto',
        'Renacimiento',
        'Barroco',
        'Rococó',
        'Romanticismo',
        'Neoclasicismo',
        'Modernismo',
        'Arte Pop',
        'Arte Naïf'
    ];

    public static function getTecnica(){return self::$tecnicasArte;}
    public static function getCategoria(){return self::$categorias;}
    
    public function artista(){
        return $this->belongsTo(Artista::class, 'id');
    }

    public function factura(){
        return $this->hasOne(Factura::class, 'idObra');       
    }
}
