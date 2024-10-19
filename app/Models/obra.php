<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Obra extends Model
{
    use HasFactory;

    protected $table = "obras";

    protected static $tecnicasArte = [
        'Oil on canvas',
        'Watercolor',
        'Watercolor on paper',
        'Tempera',
        'Pastel painting',
        'Fresco painting',
        'Digital painting',
        'Wood carving',
        'Marble sculpture',
        'Engraving',
        'Serigraphy',
        'Art photography',
        'Digital art',
        'Collage',
        'Pyrography',
        'Bronze sculpture'
    ];

    protected static $categorias = [
    'Cubism',
    'Impressionism',
    'Expressionism',
    'Realism',
    'Surrealism',
    'Abstract',
    'Renaissance',
    'Baroque',
    'Rococo',
    'Romanticism',
    'Neoclassicism',
    'Modernism',
    'Pop Art',
    'Naïve Art'
    ];

    public static function getTecnica(){return self::$tecnicasArte;}
    public static function getCategoria(){return self::$categorias;}
    
    public function artista(){
        return $this->belongsTo(Artista::class, 'idArtista');
    }

    public function detallesFactura(){
        return $this->belongsTo(DetalleFactura::class, 'idObra');
    }

    
}
