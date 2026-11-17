<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstimasiBiaya extends Model
{
    use HasFactory;

    protected $table = 'estimasi_biayas';

    protected $fillable = [
        'kode_estimasi',
        'booking_id',
        'owner_id',
        'nama_owner',
        'telepon',
        'nama_hewan',
        'jenis_hewan',
        'berat_hewan',
        'dokter',
        'subtotal_layanan',
        'subtotal_obat',
        'diskon',
        'pajak',
        'grand_total',
        'catatan',
        'items',
        'created_by',
    ];

    protected $casts = [
        'items' => 'array',
        'subtotal_layanan' => 'float',
        'subtotal_obat' => 'float',
        'diskon' => 'float',
        'pajak' => 'float',
        'grand_total' => 'float',
    ];

    public function booking()
    {
        return $this->belongsTo(booking::class, 'booking_id');
    }

    public function owner()
    {
        return $this->belongsTo(Owner::class, 'owner_id');
    }
}
