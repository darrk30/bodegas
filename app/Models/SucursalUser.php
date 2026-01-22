<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SucursalUser extends Model
{
    protected $fillable = [
        'user_id',
        'sucursal_id',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

}
