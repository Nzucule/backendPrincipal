<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    use HasFactory;

    protected $table = 'horarios';

    protected $fillable = [
        'barbeiro_id',
        'dia_semana',
        'hora_inicio',
        'hora_fim',
    ];

     public function barbeiro()
    {
        return $this->belongsTo(User::class, 'barbeiro_id');
    }
}

