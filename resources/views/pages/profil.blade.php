<?php

use Illuminate\Validation\Rule;
use Livewire\Component;

new class extends Component {
    public string $name = '';
    public ?string $email = '';
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount(): void
    {
        $this->name = auth()->user()->name;
        $this->email = auth()->user()->email;
    }

    public function saveProfile(): void
    {
        $user = auth()->user();
        $data = $this->validate([
            'name' => 'required|string|max:255',
            'email' => ['nullable', 'email', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->update($data);
        session()->flash('success', 'Profil berhasil diperbarui.');
    }

    public function savePassword(): void
    {
        $this->validate([
            'current_password' => ['required', 'current_password'],
            'password' => 'required|min:8|confirmed',
        ]);

        auth()->user()->update(['password' => $this->password]);
        $this->reset('current_password', 'password', 'password_confirmation');
        session()->flash('success', 'Password berhasil diubah.');
    }
}; ?>

@php
    $user = auth()->user();
    $role = $user->getRoleNames()->first();
    $roleLabel = match ($role) {
        'admin' => 'Administrator',
        'guru' => 'Guru',
        'siswa' => 'Siswa',
        'kepala_sekolah' => 'Kepala Sekolah',
        default => 'Pengguna',
    };
    $initials = str($user->name)->explode(' ')->filter()->take(2)->map(fn ($part) => str($part)->substr(0, 1)->upper())->implode('');
@endphp

<div>
    <div class="mb-6">
        <p class="text-sm font-semibold text-sky-700">Akun</p>
        <h1 class="page-title">Profil Saya</h1>
        <p class="page-subtitle">Kelola identitas akun dan pastikan keamanan akses Anda tetap terjaga.</p>
    </div>

    <section class="content-hero mb-6" aria-labelledby="profile-summary-title">
        <div class="relative z-10 flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex min-w-0 items-center gap-4 sm:gap-5">
                <div class="grid size-16 shrink-0 place-items-center rounded-2xl bg-sky-600 text-xl font-bold text-white shadow-lg shadow-sky-200 sm:size-20 sm:text-2xl" aria-hidden="true">
                    {{ $initials ?: 'U' }}
                </div>
                <div class="min-w-0">
                    <div class="mb-2 flex flex-wrap items-center gap-2">
                        <span class="inline-flex rounded-full bg-sky-100 px-3 py-1 text-xs font-bold text-sky-800">{{ $roleLabel }}</span>
                        <span class="{{ $user->status === 'aktif' ? 'badge-active' : 'badge-inactive' }}">{{ ucfirst($user->status) }}</span>
                    </div>
                    <h2 id="profile-summary-title" class="truncate text-xl font-bold tracking-tight text-slate-950 sm:text-2xl">{{ $user->name }}</h2>
                    <p class="mt-1 truncate text-sm text-slate-600">{{ $user->email ?: 'Email belum ditambahkan' }}</p>
                </div>
            </div>

            <dl class="grid gap-px overflow-hidden rounded-xl border border-slate-200 bg-slate-200 sm:grid-cols-2 lg:min-w-[28rem]">
                <div class="bg-white/95 px-4 py-3">
                    <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Username</dt>
                    <dd class="mt-1 truncate text-sm font-semibold text-slate-800">{{ $user->username }}</dd>
                </div>
                <div class="bg-white/95 px-4 py-3">
                    <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Login Terakhir</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-800">{{ $user->last_login_at?->translatedFormat('d F Y, H:i') ?? 'Belum tercatat' }}</dd>
                </div>
            </dl>
        </div>
    </section>

    <div class="grid items-start gap-6 xl:grid-cols-2">
        <form wire:submit="saveProfile" class="card overflow-hidden" aria-labelledby="profile-form-title">
            <div class="flex items-start gap-4 border-b border-slate-100 bg-slate-50/60 px-5 py-5 sm:px-6">
                <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-sky-100 text-sky-700">
                    <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                </span>
                <div>
                    <h2 id="profile-form-title" class="text-lg font-bold text-slate-950">Informasi Profil</h2>
                    <p class="mt-1 text-sm leading-6 text-slate-600">Informasi ini digunakan untuk mengenali akun Anda di dalam aplikasi.</p>
                </div>
            </div>

            <div class="space-y-5 p-5 sm:p-6">
                <div>
                    <label for="profile-name" class="form-label">Nama Lengkap <span>*</span></label>
                    <input id="profile-name" wire:model="name" class="form-input" autocomplete="name" autofocus>
                    <p class="mt-2 text-xs leading-5 text-slate-500">Gunakan nama yang mudah dikenali oleh pengguna sekolah.</p>
                    @error('name')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="profile-email" class="form-label">Alamat Email</label>
                    <input id="profile-email" type="email" wire:model="email" class="form-input" autocomplete="email" inputmode="email" placeholder="nama@contoh.com">
                    <p class="mt-2 text-xs leading-5 text-slate-500">Email bersifat opsional dan dapat digunakan untuk kebutuhan pemulihan akun.</p>
                    @error('email')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <div class="flex items-start gap-3">
                        <svg viewBox="0 0 24 24" class="mt-0.5 size-5 shrink-0 text-slate-500" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7h.01"/></svg>
                        <p class="text-sm leading-6 text-slate-600">Username dan jenis akses hanya dapat diubah oleh Administrator melalui menu Pengguna.</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end border-t border-slate-100 bg-slate-50/50 px-5 py-4 sm:px-6">
                <button type="submit" class="btn-primary w-full sm:w-auto" wire:loading.attr="disabled" wire:target="saveProfile">
                    <svg wire:loading.remove wire:target="saveProfile" viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12.5 9.5 17 19 7.5"/></svg>
                    <span wire:loading.remove wire:target="saveProfile">Simpan Profil</span>
                    <span wire:loading wire:target="saveProfile">Menyimpan...</span>
                </button>
            </div>
        </form>

        <form wire:submit="savePassword" class="card overflow-hidden" aria-labelledby="password-form-title" x-data="{ currentVisible: false, newVisible: false, confirmationVisible: false }">
            <div class="flex items-start gap-4 border-b border-slate-100 bg-slate-50/60 px-5 py-5 sm:px-6">
                <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-emerald-100 text-emerald-700">
                    <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"/></svg>
                </span>
                <div>
                    <h2 id="password-form-title" class="text-lg font-bold text-slate-950">Keamanan Password</h2>
                    <p class="mt-1 text-sm leading-6 text-slate-600">Gunakan password baru yang kuat dan berbeda dari password sebelumnya.</p>
                </div>
            </div>

            <div class="space-y-5 p-5 sm:p-6">
                <div>
                    <label for="current-password" class="form-label">Password Saat Ini <span>*</span></label>
                    <div class="relative">
                        <input id="current-password" :type="currentVisible ? 'text' : 'password'" wire:model="current_password" class="form-input pr-14" autocomplete="current-password">
                        <button type="button" @click="currentVisible = !currentVisible" class="absolute right-0 top-0 grid size-11 place-items-center rounded-xl text-slate-500 transition hover:bg-slate-100 hover:text-sky-700" :aria-label="currentVisible ? 'Sembunyikan password saat ini' : 'Tampilkan password saat ini'" :aria-pressed="currentVisible.toString()">
                            <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                        </button>
                    </div>
                    @error('current_password')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="new-password" class="form-label">Password Baru <span>*</span></label>
                    <div class="relative">
                        <input id="new-password" :type="newVisible ? 'text' : 'password'" wire:model="password" class="form-input pr-14" autocomplete="new-password">
                        <button type="button" @click="newVisible = !newVisible" class="absolute right-0 top-0 grid size-11 place-items-center rounded-xl text-slate-500 transition hover:bg-slate-100 hover:text-sky-700" :aria-label="newVisible ? 'Sembunyikan password baru' : 'Tampilkan password baru'" :aria-pressed="newVisible.toString()">
                            <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                        </button>
                    </div>
                    <p class="mt-2 text-xs leading-5 text-slate-500">Minimal 8 karakter. Hindari menggunakan nama, username, atau informasi yang mudah ditebak.</p>
                    @error('password')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password-confirmation" class="form-label">Konfirmasi Password Baru <span>*</span></label>
                    <div class="relative">
                        <input id="password-confirmation" :type="confirmationVisible ? 'text' : 'password'" wire:model="password_confirmation" class="form-input pr-14" autocomplete="new-password">
                        <button type="button" @click="confirmationVisible = !confirmationVisible" class="absolute right-0 top-0 grid size-11 place-items-center rounded-xl text-slate-500 transition hover:bg-slate-100 hover:text-sky-700" :aria-label="confirmationVisible ? 'Sembunyikan konfirmasi password' : 'Tampilkan konfirmasi password'" :aria-pressed="confirmationVisible.toString()">
                            <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                        </button>
                    </div>
                </div>

                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3">
                    <div class="flex items-start gap-3">
                        <svg viewBox="0 0 24 24" class="mt-0.5 size-5 shrink-0 text-emerald-700" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 3 5 6v5c0 4.7 2.8 8 7 10 4.2-2 7-5.3 7-10V6l-7-3Z"/><path d="m9 12 2 2 4-4"/></svg>
                        <p class="text-sm leading-6 text-emerald-800">Sistem meminta password saat ini untuk memastikan perubahan dilakukan oleh pemilik akun.</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end border-t border-slate-100 bg-slate-50/50 px-5 py-4 sm:px-6">
                <button type="submit" class="btn-primary w-full sm:w-auto" wire:loading.attr="disabled" wire:target="savePassword">
                    <svg wire:loading.remove wire:target="savePassword" viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12.5 9.5 17 19 7.5"/></svg>
                    <span wire:loading.remove wire:target="savePassword">Perbarui Password</span>
                    <span wire:loading wire:target="savePassword">Memperbarui...</span>
                </button>
            </div>
        </form>
    </div>
</div>
