<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    protected $table = 'semester';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['tanggal_mulai' => 'date', 'tanggal_selesai' => 'date'];
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function pengajaran()
    {
        return $this->hasMany(Pengajaran::class);
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }
}
