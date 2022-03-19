<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class mJadwal extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "tb_jadwal";

    protected $fillable = [
        'waktu_berangkat',
        'id_asal_pelabuhan',
        'id_tujuan_pelabuhan',
        'estimasi_waktu',
        'id_kapal',
        'harga',
    ];

    protected $dates = ['deleted_at'];

    public function JPelabuhanAsal()
    {
        return $this->belongsTo('App\Models\mPelabuhan', 'id_asal_pelabuhan');
    }

    public function JPelabuhanTujuan()
    {
        return $this->belongsTo('App\Models\mPelabuhan', 'id_tujuan_pelabuhan');
    }

    public function JDetailJadwal()
    {
        return $this->hasMany('App\Models\mDetailJadwal', 'id_jadwal');
    }

    public function JKapal()
    {
        return $this->belongsTo('App\Models\mKapal','id_kapal');
    }
}
