<?php

namespace Database\Seeders\Support;

use RuntimeException;

class DemoCatalog
{
    public const PASSWORD = '123';

    public static function school(): array
    {
        return [
            'nama_sekolah' => 'SD Negeri 232 Maluku Tengah',
            'npsn' => '60100000',
            'alamat' => 'Kabupaten Maluku Tengah, Maluku',
        ];
    }

    public static function academicYear(): array
    {
        return [
            'nama' => '2026/2027',
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2027-06-30',
            'semesters' => [
                ['nama' => 'ganjil', 'tanggal_mulai' => '2026-07-01', 'tanggal_selesai' => '2026-12-20', 'status' => 'aktif'],
                ['nama' => 'genap', 'tanggal_mulai' => '2027-01-05', 'tanggal_selesai' => '2027-06-30', 'status' => 'nonaktif'],
            ],
        ];
    }

    /**
     * Data guru dari dokumen sekolah. Urutan menentukan username guru1, guru2, dst.
     * Wali kelas hanya mengajar kelasnya sendiri; guru mapel hanya mapel keahliannya.
     *
     * @return list<array<string, mixed>>
     */
    public static function teachers(): array
    {
        return [
            ['nama_lengkap' => 'Surjani Samual, S.Pd', 'nip' => '197005021999032004', 'jenis_kelamin' => 'perempuan', 'peran' => 'wali', 'wali_kelas' => 'I A'],
            ['nama_lengkap' => 'Lin Pary, S.Pd.SD', 'nip' => '197104242006042025', 'jenis_kelamin' => 'perempuan', 'peran' => 'wali', 'wali_kelas' => 'II A'],
            ['nama_lengkap' => 'Patiah Lessy, S.Pd', 'nip' => '196605162007012023', 'jenis_kelamin' => 'perempuan', 'peran' => 'wali', 'wali_kelas' => 'III A'],
            ['nama_lengkap' => 'Murni Hatan, S.Pd', 'nip' => '197107251999102001', 'jenis_kelamin' => 'perempuan', 'peran' => 'wali', 'wali_kelas' => 'IV A'],
            ['nama_lengkap' => 'Sitti Rohani Lessy, S.Pd', 'nip' => '197411052007012015', 'jenis_kelamin' => 'perempuan', 'peran' => 'wali', 'wali_kelas' => 'V A'],
            ['nama_lengkap' => 'Amanah Ihsani, S.Pd', 'nip' => '197803142025212011', 'jenis_kelamin' => 'perempuan', 'peran' => 'wali', 'wali_kelas' => 'VI A'],
            ['nama_lengkap' => 'Wa Maisara, S.Pd.I', 'nip' => '197401052008012015', 'jenis_kelamin' => 'perempuan', 'peran' => 'mapel', 'mapel' => 'Pendidikan Agama', 'kelas' => ['I A']],
            ['nama_lengkap' => 'Wa Sitia, S.Pd.I', 'nip' => '197203122008012017', 'jenis_kelamin' => 'perempuan', 'peran' => 'mapel', 'mapel' => 'Pendidikan Agama', 'kelas' => ['II A', 'III A']],
            ['nama_lengkap' => 'Siti R. Rehalat, S.Pd.I', 'nip' => '196711171992122002', 'jenis_kelamin' => 'perempuan', 'peran' => 'mapel', 'mapel' => 'Pendidikan Agama', 'kelas' => ['IV A', 'V A']],
            ['nama_lengkap' => 'Siti Hawa Lessy, S.Pd.I', 'nip' => null, 'jenis_kelamin' => 'perempuan', 'peran' => 'mapel', 'mapel' => 'Pendidikan Agama', 'kelas' => ['VI A']],
            ['nama_lengkap' => 'Fitriyani, S.Pd', 'nip' => null, 'jenis_kelamin' => 'perempuan', 'peran' => 'mapel', 'mapel' => 'PJOK', 'kelas' => '*'],
            ['nama_lengkap' => 'Surnida Rehalat, S.Pd', 'nip' => null, 'jenis_kelamin' => 'perempuan', 'peran' => 'mapel', 'mapel' => 'Bahasa Inggris', 'kelas' => '*'],
            ['nama_lengkap' => 'Sonita Ibrahim', 'nip' => null, 'jenis_kelamin' => 'perempuan', 'peran' => 'mapel', 'mapel' => 'Seni Budaya', 'kelas' => '*'],
        ];
    }

    public static function classes(): array
    {
        return [
            ['nama' => 'I A', 'tingkat' => 1],
            ['nama' => 'II A', 'tingkat' => 2],
            ['nama' => 'III A', 'tingkat' => 3],
            ['nama' => 'IV A', 'tingkat' => 4],
            ['nama' => 'V A', 'tingkat' => 5],
            ['nama' => 'VI A', 'tingkat' => 6],
        ];
    }

    public static function subjects(): array
    {
        return [
            ['kode' => 'MP-01', 'nama' => 'Pendidikan Agama'],
            ['kode' => 'MP-02', 'nama' => 'Pendidikan Pancasila'],
            ['kode' => 'MP-03', 'nama' => 'Bahasa Indonesia'],
            ['kode' => 'MP-04', 'nama' => 'Matematika'],
            ['kode' => 'MP-05', 'nama' => 'IPAS'],
            ['kode' => 'MP-06', 'nama' => 'Seni Budaya'],
            ['kode' => 'MP-07', 'nama' => 'PJOK'],
            ['kode' => 'MP-08', 'nama' => 'Bahasa Inggris'],
            ['kode' => 'MP-09', 'nama' => 'Muatan Lokal'],
            ['kode' => 'MP-10', 'nama' => 'Literasi'],
        ];
    }

    public static function homeroomSubjects(): array
    {
        return [
            'Pendidikan Pancasila',
            'Bahasa Indonesia',
            'Matematika',
            'IPAS',
            'Muatan Lokal',
            'Literasi',
        ];
    }

    public static function lessonPeriods(): array
    {
        return [
            ['nama' => 'Jam ke 1', 'jam_mulai' => '07:30', 'jam_selesai' => '08:10', 'jenis' => 'pelajaran'],
            ['nama' => 'Jam ke 2', 'jam_mulai' => '08:10', 'jam_selesai' => '08:50', 'jenis' => 'pelajaran'],
            ['nama' => 'Istirahat', 'jam_mulai' => '08:50', 'jam_selesai' => '09:10', 'jenis' => 'istirahat'],
            ['nama' => 'Jam ke 3', 'jam_mulai' => '09:10', 'jam_selesai' => '09:50', 'jenis' => 'pelajaran'],
            ['nama' => 'Jam ke 4', 'jam_mulai' => '09:50', 'jam_selesai' => '10:30', 'jenis' => 'pelajaran'],
        ];
    }

    public static function weekdays(): array
    {
        return ['senin', 'selasa', 'rabu', 'kamis', 'jumat'];
    }

    public static function periodsPerSubject(): int
    {
        return 2;
    }

    public static function studentsPerClass(): int
    {
        return 5;
    }

    public static function demoStudent(): array
    {
        return [
            'kelas' => 'I A',
            'nama_lengkap' => 'Ahmad Fauzan Lessy',
            'jenis_kelamin' => 'laki-laki',
            'tempat_lahir' => 'Masohi',
            'username' => 'siswa',
            'email' => 'siswa@sisd232.test',
        ];
    }

    /**
     * @return list<array{kelas: string, mapel: string, guru: string}>
     */
    public static function teachingAssignments(): array
    {
        $classNames = array_column(self::classes(), 'nama');
        $assignments = [];

        foreach (self::teachers() as $teacher) {
            if (($teacher['peran'] ?? '') === 'wali') {
                foreach (self::homeroomSubjects() as $subject) {
                    $assignments[] = [
                        'kelas' => $teacher['wali_kelas'],
                        'mapel' => $subject,
                        'guru' => $teacher['nama_lengkap'],
                    ];
                }
            }

            if (($teacher['peran'] ?? '') === 'mapel') {
                $classes = $teacher['kelas'] === '*' ? $classNames : $teacher['kelas'];
                foreach ($classes as $className) {
                    $assignments[] = [
                        'kelas' => $className,
                        'mapel' => $teacher['mapel'],
                        'guru' => $teacher['nama_lengkap'],
                    ];
                }
            }
        }

        return $assignments;
    }

    public static function assertConsistent(): void
    {
        $classNames = array_column(self::classes(), 'nama');
        $subjectNames = array_column(self::subjects(), 'nama');
        $teacherNames = array_column(self::teachers(), 'nama_lengkap');
        $waliByClass = [];

        if (count($teacherNames) !== count(array_unique($teacherNames))) {
            throw new RuntimeException('Nama guru pada katalog seeder tidak boleh duplikat.');
        }

        foreach (self::teachers() as $teacher) {
            if (($teacher['peran'] ?? '') === 'wali') {
                $className = $teacher['wali_kelas'] ?? null;
                if (! in_array($className, $classNames, true)) {
                    throw new RuntimeException("Wali {$teacher['nama_lengkap']} menunjuk kelas yang tidak ada: {$className}.");
                }
                if (isset($waliByClass[$className])) {
                    throw new RuntimeException("Kelas {$className} memiliki lebih dari satu wali kelas.");
                }
                $waliByClass[$className] = $teacher['nama_lengkap'];
            }

            if (($teacher['peran'] ?? '') === 'mapel') {
                if (! in_array($teacher['mapel'], $subjectNames, true)) {
                    throw new RuntimeException("Mapel {$teacher['mapel']} untuk {$teacher['nama_lengkap']} tidak ada di katalog.");
                }
                if (in_array($teacher['mapel'], self::homeroomSubjects(), true)) {
                    throw new RuntimeException("Mapel {$teacher['mapel']} tidak boleh diambil guru mapel karena milik wali kelas.");
                }
            }
        }

        foreach ($classNames as $className) {
            if (! isset($waliByClass[$className])) {
                throw new RuntimeException("Kelas {$className} belum memiliki wali kelas.");
            }
        }

        $pairs = [];
        foreach (self::teachingAssignments() as $assignment) {
            if (! in_array($assignment['kelas'], $classNames, true) || ! in_array($assignment['mapel'], $subjectNames, true) || ! in_array($assignment['guru'], $teacherNames, true)) {
                throw new RuntimeException("Penugasan tidak valid: {$assignment['guru']} / {$assignment['mapel']} / {$assignment['kelas']}.");
            }

            $key = $assignment['kelas'].'|'.$assignment['mapel'];
            if (isset($pairs[$key])) {
                throw new RuntimeException("Kelas {$assignment['kelas']} memiliki dua guru untuk {$assignment['mapel']}.");
            }
            $pairs[$key] = $assignment['guru'];
        }

        foreach ($classNames as $className) {
            foreach ($subjectNames as $subject) {
                if (! isset($pairs[$className.'|'.$subject])) {
                    throw new RuntimeException("Kelas {$className} belum memiliki guru untuk {$subject}.");
                }
            }
        }
    }
}
