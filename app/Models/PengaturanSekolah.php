<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanSekolah extends Model
{
    protected $table = 'pengaturan_sekolah';

    protected $guarded = ['id'];

    public function kepalaSekolah()
    {
        return $this->belongsTo(User::class, 'kepala_sekolah_user_id');
    }
}
