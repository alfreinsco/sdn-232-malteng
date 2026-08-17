<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JamPelajaran extends Model
{
    protected $table = 'jam_pelajaran';

    protected $guarded = ['id'];

    public function jadwal()
    {
        return $this->hasMany(JadwalPelajaran::class);
    }
}
