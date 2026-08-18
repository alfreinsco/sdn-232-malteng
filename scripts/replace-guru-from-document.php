<?php

declare(strict_types=1);

use App\Models\Guru;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

require dirname(__DIR__).'/vendor/autoload.php';

$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$teachers = [
    ['nama_lengkap' => 'Surjani Samual, S.Pd', 'nip' => '197005021999032004'],
    ['nama_lengkap' => 'Lin Pary, S.Pd.SD', 'nip' => '197104242006042025'],
    ['nama_lengkap' => 'Patiah Lessy, S.Pd', 'nip' => '196605162007012023'],
    ['nama_lengkap' => 'Murni Hatan, S.Pd', 'nip' => '197107251999102001'],
    ['nama_lengkap' => 'Wa Maisara, S.Pd.I', 'nip' => '197401052008012015'],
    ['nama_lengkap' => 'Wa Sitia, S.Pd.I', 'nip' => '197203122008012017'],
    ['nama_lengkap' => 'Siti R. Rehalat, S.Pd.I', 'nip' => '196711171992122002'],
    ['nama_lengkap' => 'Sitti Rohani Lessy, S.Pd', 'nip' => '197411052007012015'],
    ['nama_lengkap' => 'Amanah Ihsani, S.Pd', 'nip' => '197803142025212011'],
    ['nama_lengkap' => 'Fitriyani, S.Pd', 'nip' => null],
    ['nama_lengkap' => 'Surnida Rehalat, S.Pd', 'nip' => null],
    ['nama_lengkap' => 'Siti Hawa Lessy, S.Pd.I', 'nip' => null],
    ['nama_lengkap' => 'Sonita Ibrahim', 'nip' => null],
];

$oldTeachers = Guru::query()->orderBy('id')->get();
$oldTeacherIds = $oldTeachers->pluck('id');
$oldUserIds = $oldTeachers->pluck('user_id')->filter()->values();
$oldTeachingIds = DB::table('pengajaran')->whereIn('guru_id', $oldTeacherIds)->pluck('id');

$summary = [
    'old_teachers' => $oldTeachers->count(),
    'old_teacher_users' => $oldUserIds->count(),
    'homeroom_assignments' => DB::table('kelas')->whereIn('wali_kelas_id', $oldTeacherIds)->count(),
    'teachings' => $oldTeachingIds->count(),
    'schedules' => DB::table('jadwal_pelajaran')->whereIn('pengajaran_id', $oldTeachingIds)->count(),
    'grades' => DB::table('nilai_tugas')->whereIn('pengajaran_id', $oldTeachingIds)->count(),
    'new_teachers' => count($teachers),
];

if (! in_array('--apply', $argv, true)) {
    echo json_encode(['mode' => 'dry-run', 'summary' => $summary, 'teachers' => $teachers], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL;
    exit(0);
}

$backupDirectory = storage_path('app/backups');
File::ensureDirectoryExists($backupDirectory);
$backupPath = $backupDirectory.'/guru-before-replacement-'.now()->format('Ymd-His').'.json';

$backup = [
    'created_at' => now()->toIso8601String(),
    'source_document' => 'DATA GURU SD NEGERI 232 MALTENG.docx',
    'summary' => $summary,
    'tables' => [
        'guru' => $oldTeachers->toArray(),
        'users' => User::query()->whereIn('id', $oldUserIds)->get()->makeVisible(['password', 'remember_token'])->toArray(),
        'model_has_roles' => DB::table('model_has_roles')->where('model_type', User::class)->whereIn('model_id', $oldUserIds)->get()->toArray(),
        'kelas' => DB::table('kelas')->whereIn('wali_kelas_id', $oldTeacherIds)->get()->toArray(),
        'pengajaran' => DB::table('pengajaran')->whereIn('id', $oldTeachingIds)->get()->toArray(),
        'jadwal_pelajaran' => DB::table('jadwal_pelajaran')->whereIn('pengajaran_id', $oldTeachingIds)->get()->toArray(),
        'nilai_tugas' => DB::table('nilai_tugas')->whereIn('pengajaran_id', $oldTeachingIds)->get()->toArray(),
    ],
];

File::put($backupPath, json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

DB::transaction(function () use ($oldTeacherIds, $oldTeachingIds, $oldUserIds, $teachers): void {
    DB::table('kelas')->whereIn('wali_kelas_id', $oldTeacherIds)->update(['wali_kelas_id' => null]);
    DB::table('nilai_tugas')->whereIn('pengajaran_id', $oldTeachingIds)->delete();
    DB::table('jadwal_pelajaran')->whereIn('pengajaran_id', $oldTeachingIds)->delete();
    DB::table('pengajaran')->whereIn('id', $oldTeachingIds)->delete();
    Guru::query()->whereIn('id', $oldTeacherIds)->delete();
    User::query()->whereIn('id', $oldUserIds)->delete();

    foreach ($teachers as $teacher) {
        Guru::query()->create([
            ...$teacher,
            'status' => 'aktif',
        ]);
    }
});

echo json_encode([
    'mode' => 'applied',
    'backup' => $backupPath,
    'before' => $summary,
    'after' => [
        'teachers' => Guru::query()->count(),
        'teacher_users' => User::role('guru')->count(),
        'teachings' => DB::table('pengajaran')->count(),
        'schedules' => DB::table('jadwal_pelajaran')->count(),
        'grades' => DB::table('nilai_tugas')->count(),
    ],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL;
