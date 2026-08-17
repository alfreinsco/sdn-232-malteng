<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    use HasFactory;

    protected $table = 'guru';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['tanggal_lahir' => 'date'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pengajaran()
    {
        return $this->hasMany(Pengajaran::class);
    }

    public function kelasWali()
    {
        return $this->hasMany(Kelas::class, 'wali_kelas_id');
    }
}
