<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recurso extends Model
{
    use HasFactory;

    protected $fillable = ['inscricao_id', 'numero_recurso', 'tipo', 'arquivo'];

    // public function inscricao()
    // {
    //     return $this->belongsTo(Inscricao::class);
    // }
}
