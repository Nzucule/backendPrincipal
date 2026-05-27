<?php
// app/Models/Servico.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servico extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome', 'descricao', 'preco', 'duracao', 
        'imagem', 'categoria', 'tipo_servico', 
        'beneficios', 'detalhes'
    ];

    protected $casts = [
        'beneficios' => 'array',
        'detalhes' => 'array'
    ];


    // 👇 ADICIONE ESTE ACCESSOR
    protected $appends = ['imagem_url'];

    public function getImagemUrlAttribute()
    {
        return $this->imagem ? asset('storage/' . $this->imagem) : null;
    }

    public function agendamentos()
    {
        return $this->hasMany(Agendamento::class);
    }
}