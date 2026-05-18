<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SucursalUser extends Model
{
    protected $table = 'sucursal_user';
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
