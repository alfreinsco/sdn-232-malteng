<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaturan_sekolah', function (Blueprint $table) {
            $table->id();
            $table->string('nama_sekolah');
            $table->string('npsn')->nullable();
            $table->text('alamat')->nullable();
            $table->string('telepon')->nullable();
            $table->string('email')->nullable();
            $table->string('logo')->nullable();
            $table->foreignId('kepala_sekolah_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('tahun_ajaran', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->string('status')->default('nonaktif')->index();
            $table->timestamps();
        });

        Schema::create('semester', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->restrictOnDelete();
            $table->string('nama');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->string('status')->default('nonaktif')->index();
            $table->timestamps();
            $table->unique(['tahun_ajaran_id', 'nama']);
        });

        Schema::create('guru', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->string('nip')->nullable()->unique();
            $table->string('nuptk')->nullable()->unique();
            $table->string('nama_lengkap');
            $table->string('jenis_kelamin')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('telepon')->nullable();
            $table->text('alamat')->nullable();
            $table->string('status')->default('aktif')->index();
            $table->timestamps();
            $table->index('nama_lengkap');
        });

        Schema::create('siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->string('nis')->nullable()->unique();
            $table->string('nisn')->nullable()->unique();
            $table->string('nama_lengkap');
            $table->string('jenis_kelamin')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->text('alamat')->nullable();
            $table->string('status')->default('aktif')->index();
            $table->timestamps();
            $table->index('nama_lengkap');
        });

        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->restrictOnDelete();
            $table->string('nama');
            $table->unsignedTinyInteger('tingkat');
            $table->foreignId('wali_kelas_id')->nullable()->constrained('guru')->nullOnDelete();
            $table->string('status')->default('aktif')->index();
            $table->timestamps();
            $table->unique(['tahun_ajaran_id', 'nama']);
        });

        Schema::create('siswa_kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->restrictOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->restrictOnDelete();
            $table->string('status')->default('aktif')->index();
            $table->timestamps();
            $table->unique(['siswa_id', 'kelas_id']);
        });

        Schema::create('mata_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->nullable()->unique();
            $table->string('nama')->unique();
            $table->string('status')->default('aktif')->index();
            $table->timestamps();
        });

        Schema::create('jam_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->unsignedSmallInteger('urutan')->unique();
            $table->string('jenis')->default('pelajaran');
            $table->string('status')->default('aktif')->index();
            $table->timestamps();
        });

        Schema::create('pengajaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('semester_id')->constrained('semester')->restrictOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->restrictOnDelete();
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajaran')->restrictOnDelete();
            $table->foreignId('guru_id')->constrained('guru')->restrictOnDelete();
            $table->string('status')->default('aktif')->index();
            $table->timestamps();
            $table->unique(['semester_id', 'kelas_id', 'mata_pelajaran_id', 'guru_id'], 'pengajaran_unique');
            $table->index(['semester_id', 'guru_id']);
        });

        Schema::create('jadwal_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajaran_id')->constrained('pengajaran')->restrictOnDelete();
            $table->string('hari')->index();
            $table->foreignId('jam_pelajaran_id')->constrained('jam_pelajaran')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['pengajaran_id', 'hari', 'jam_pelajaran_id'], 'jadwal_unique');
            $table->index(['hari', 'jam_pelajaran_id']);
        });

        Schema::create('nilai_tugas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajaran_id')->constrained('pengajaran')->restrictOnDelete();
            $table->foreignId('siswa_id')->constrained('siswa')->restrictOnDelete();
            $table->unsignedTinyInteger('bulan');
            $table->unsignedTinyInteger('minggu');
            $table->decimal('nilai', 5, 2)->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['pengajaran_id', 'siswa_id', 'bulan', 'minggu'], 'nilai_tugas_unique');
            $table->index(['bulan', 'minggu']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilai_tugas');
        Schema::dropIfExists('jadwal_pelajaran');
        Schema::dropIfExists('pengajaran');
        Schema::dropIfExists('jam_pelajaran');
        Schema::dropIfExists('mata_pelajaran');
        Schema::dropIfExists('siswa_kelas');
        Schema::dropIfExists('kelas');
        Schema::dropIfExists('siswa');
        Schema::dropIfExists('guru');
        Schema::dropIfExists('semester');
        Schema::dropIfExists('tahun_ajaran');
        Schema::dropIfExists('pengaturan_sekolah');
    }
};
