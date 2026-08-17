<?php

namespace App\Enums;

enum RoleName: string
{
    case Admin = 'admin';
    case Guru = 'guru';
    case Siswa = 'siswa';
    case KepalaSekolah = 'kepala_sekolah';
}
