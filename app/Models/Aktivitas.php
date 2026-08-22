<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

class Aktivitas extends Model
{
    use MassPrunable;

    protected $table = 'aktivitas';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['properties' => 'array'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        return $this->morphTo();
    }

    public function prunable(): Builder
    {
        return static::query()->where('created_at', '<', now()->subDays(config('activity.retention_days')));
    }
}
