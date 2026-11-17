<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Owner extends Model
{
    use HasFactory;

    protected $table = 'owners';

    protected $fillable = [
        'nama',
        'telepon',
        'email',
        'alamat',
        'catatan_khusus',
    ];

    /**
     * Relasi ke hewan peliharaan.
     * Satu owner dapat memiliki banyak hewan.
     */
    public function pets()
    {
        return $this->hasMany(OwnerPet::class, 'owner_id');
    }
}