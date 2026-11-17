<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'owner_pet_id',
        'kode_booking',
        'tipe_kedatangan',
        'jam',
        'nama',
        'nama_hewan',
        'berat_hewan',
        'jenis_hewan',
        'alamat',
        'telpon',
        'tanggal',
        'keluar',
        'keluhan',
        'karyawan1',
        'karyawan2',
        'karyawan3',
        'suhu_tubuh',
        'denyut_jantung',
        'crt',
        'subjective',
        'objective',
        'assessment',
        'plan',
        'foto_klinis',
        'tanggal_kontrol',
        'vaksin',
        'vaksin_kuantitas',
        'vaksin_harga',
        'grooming',
        'grooming_kuantitas',
        'grooming_harga',
        'operasi',
        'operasi_kuantitas',
        'operasi_harga',
        'lainnya',
        'lainnya_kuantitas',
        'lainnya_harga',
        'jasa_dokter',
        'jasa_dokter_kuantitas',
        'jasa_dokter_harga',
        'diagnosa_penunjang',
        'diagnosa_penunjang_kuantitas',
        'diagnosa_penunjang_harga',
        'transportasi',
        'transportasi_kuantitas',
        'transport_harga',
        'total_transport',
        'dp',
        'diskon',
        'pajak',
        'total',
        'metode_pembayaran',
        'status',
    ];

    public function owner()
    {
        return $this->belongsTo(Owner::class, 'owner_id');
    }

    public function ownerPet()
    {
        return $this->belongsTo(OwnerPet::class, 'owner_pet_id');
    }

    public function transaksis()
    {
        return $this->hasMany(Transaksi::class);
    }

    public function petDailyLogs()
    {
        return $this->hasMany(PetDailyLog::class, 'booking_id');
    }
}
