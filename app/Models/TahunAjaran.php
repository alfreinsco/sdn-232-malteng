<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TahunAjaran extends Model
{
    use HasFactory;

    protected $table = 'tahun_ajaran';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['tanggal_mulai' => 'date', 'tanggal_selesai' => 'date'];
    }

    public function semester()
    {
        return $this->hasMany(Semester::class);
    }

    public function kelas()
    {
        return $this->hasMany(Kelas::class);
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }
}
