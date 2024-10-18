<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Taller extends Model
{
    use HasFactory;

    protected $table = "talleres";

    public function artista(){
        return $this->belongsTo(Artista::class, 'id');
    }

    protected static $categoriaTaller = [
        '3D',
        'Photograph',
        'Fashion',
        'Art',
        'UI-UX',
    ];

    public static function getCategoriaTaller(){return self::$categoriaTaller;}
}
