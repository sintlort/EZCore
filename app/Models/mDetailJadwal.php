<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class mDetailJadwal extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "tb_detail_jadwal";

    protected $fillable = [
        'id_jadwal',
        'hari',
        'status',
        'id_dermaga_asal',
        'id_dermaga_tujuan',
    ];

    protected $dates = ['deleted_at'];

    public function DJJadwal()
    {
        return $this->belongsTo('App\Models\mJadwal', 'id_jadwal');
    }

    public function DJDermagaAsal()
    {
        return $this->belongsTo('App\Models\mDermaga', 'id_dermaga_asal');
    }

    public function DJDermagaTujuan()
    {
        return $this->belongsTo('App\Models\mDermaga', 'id_dermaga_tujuan');
    }

    public function DJPembelian()
    {
        return $this->hasMany('App\Models\mPembelian','id_jadwal');
    }
}
