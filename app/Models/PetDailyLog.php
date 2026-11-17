<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PetDailyLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'owner_pet_id',
        'tanggal_log',
        'suhu_tubuh',
        'denyut_jantung',
        'berat_badan',
        'crt',
        'nafsu_makan',
        'catatan_observasi',
        'petugas',
    ];

    public function booking()
    {
        return $this->belongsTo(booking::class, 'booking_id');
    }

    public function ownerPet()
    {
        return $this->belongsTo(OwnerPet::class, 'owner_pet_id');
    }
}
