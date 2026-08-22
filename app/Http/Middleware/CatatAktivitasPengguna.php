<?php

namespace App\Http\Middleware;

use App\Services\AktivitasLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CatatAktivitasPengguna
{
    public function __construct(private readonly AktivitasLogger $logger) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        if (! $request->user() || $response->getStatusCode() >= 400 || $this->logger->hasRecorded()) {
            return $response;
        }

        $method = data_get($request->input(), 'components.0.calls.0.method');
        $labels = [
            'save' => 'Menyimpan perubahan data', 'delete' => 'Menghapus data', 'bulkDelete' => 'Menghapus beberapa data',
            'toggleStatus' => 'Mengubah status data', 'reorderJamPelajaran' => 'Mengubah urutan jam pelajaran',
            'saveProfile' => 'Memperbarui profil', 'savePassword' => 'Mengubah password akun',
        ];
        if (isset($labels[$method])) {
            $this->logger->record('aksi', $labels[$method], properties: ['livewire_method' => $method]);
        }

        return $response;
    }
}
