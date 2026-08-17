<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NilaiTugas extends Model
{
    use HasFactory;

    protected $table = 'nilai_tugas';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['nilai' => 'decimal:2'];
    }

    public function pengajaran()
    {
        return $this->belongsTo(Pengajaran::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }
}
