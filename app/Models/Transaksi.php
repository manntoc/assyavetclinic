<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    protected $fillable = ['booking_id', 'inventori_id', 'jumlah', 'total'];

    public function booking()
    {
        return $this->belongsTo(booking::class);
    }

    public function inventori()
    {
        return $this->belongsTo(Inventori::class, 'inventori_id');
    }
}
