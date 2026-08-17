<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengajaran extends Model
{
    protected $table = 'pengajaran';

    protected $guarded = ['id'];

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class);
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function jadwal()
    {
        return $this->hasMany(JadwalPelajaran::class);
    }

    public function nilaiTugas()
    {
        return $this->hasMany(NilaiTugas::class);
    }
}
