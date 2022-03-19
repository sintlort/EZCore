<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class mPembelian extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "tb_pembelian";

    protected $fillable = [
        'id_metode_pembayaran',
        'id_jadwal',
        'id_user',
        'id_golongan',
        'nomor_polisi',
        'bukti',
        'tanggal',
        'total_harga',
        'file_tiket',
        'status',
    ];

    protected $dates = ['deleted_at'];

    public function PJadwal()
    {
        return $this->belongsTo('App\Models\mDetailJadwal', 'id_jadwal');
    }

    public function PUser()
    {
        return $this->belongsTo('App\Models\mUser', 'id_user');
    }

    public function PGologan()
    {
        return $this->belongsTo('App\Models\mDetailGolongan', 'id_golongan');
    }

    public function PMetodePembayaran()
    {
        return $this->belongsTo('App\Models\mMetodePembayaran','id_metode_pembayaran');
    }
}
