<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Taller extends Model
{
    use HasFactory;

    protected $table = "talleres";


    protected static $categoriaTaller = [
        '3D',
        'Photograph',
        'Fashion',
        'Art',
        'UI-UX',
    ];

}
