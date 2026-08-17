<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    use HasFactory;

    protected $table = 'siswa';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['tanggal_lahir' => 'date'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function penempatanKelas()
    {
        return $this->hasMany(SiswaKelas::class);
    }

    public function kelas()
    {
        return $this->belongsToMany(Kelas::class, 'siswa_kelas')->withPivot('status')->withTimestamps();
    }

    public function nilaiTugas()
    {
        return $this->hasMany(NilaiTugas::class);
    }
}
