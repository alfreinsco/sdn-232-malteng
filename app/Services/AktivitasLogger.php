<?php

namespace App\Services;

use App\Models\Aktivitas;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AktivitasLogger
{
    private bool $recorded = false;
    private ?int $requestId = null;

    public function record(
        string $type,
        string $description,
        ?Model $subject = null,
        array $properties = [],
        ?User $user = null,
        ?string $actorName = null,
    ): Aktivitas {
        $this->syncRequest();
        $user ??= auth()->user();
        $request = app()->bound('request') ? request() : null;
        $activity = Aktivitas::create([
            'user_id' => $user?->id,
            'actor_name' => $actorName ?? $user?->name,
            'role' => $user?->getRoleNames()->first(),
            'type' => $type,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'description' => $description,
            'route' => $request instanceof Request ? $request->route()?->getName() : null,
            'method' => $request instanceof Request ? $request->method() : null,
            'ip_address' => $request instanceof Request ? $request->ip() : null,
            'properties' => $properties ?: null,
        ]);
        $this->recorded = true;

        return $activity;
    }

    public function hasRecorded(): bool
    {
        $this->syncRequest();

        return $this->recorded;
    }

    private function syncRequest(): void
    {
        $requestId = app()->bound('request') ? spl_object_id(request()) : null;
        if ($this->requestId !== $requestId) {
            $this->requestId = $requestId;
            $this->recorded = false;
        }
    }
}
