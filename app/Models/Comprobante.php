<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comprobante extends Model
{
    use HasFactory;

    protected $table = 'comprobante';

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function detalles()
    {
        return $this->hasMany(Detalle::class);
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'personas_id');
    }
}
