<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('aktivitas')->whereIn('type', ['akses', 'login_gagal'])->delete();

        Schema::table('aktivitas', function (Blueprint $table) {
            $table->dropColumn('user_agent');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('aktivitas', function (Blueprint $table) {
            $table->text('user_agent')->nullable();
            $table->dropIndex(['created_at']);
        });
    }
};
