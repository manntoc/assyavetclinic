<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OwnerPet extends Model
{
    use HasFactory;

    protected $table = 'owner_pets';

    protected $fillable = [
        'owner_id',
        'nama',
        'spesies',
        'jenis_hewan',
        'berat_hewan',
        'ras_hewan',
        'jenis_kelamin',
        'tanggal_lahir',
        'umur',
        'warna',
        'foto_hewan',
    ];

    /**
     * Relasi ke owner.
     * Satu hewan dimiliki oleh satu owner.
     */
    public function owner()
    {
        return $this->belongsTo(Owner::class, 'owner_id');
    }
}