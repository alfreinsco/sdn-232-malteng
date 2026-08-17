<?php

use App\Models\{PengaturanSekolah, User};
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public array $form = [];
    public $logo;

    public function mount(): void
    {
        $this->form = PengaturanSekolah::firstOrCreate(
            ['id' => 1],
            ['nama_sekolah' => 'SD Negeri 232 Maluku Tengah'],
        )->only(['nama_sekolah', 'npsn', 'alamat', 'telepon', 'email', 'kepala_sekolah_user_id']);
    }

    public function save(): void
    {
        abort_unless(auth()->user()->can('pengaturan.update'), 403);

        $data = $this->validate([
            'form.nama_sekolah' => 'required|string|max:255',
            'form.npsn' => 'nullable|string|max:30',
            'form.alamat' => 'nullable|string|max:1000',
            'form.telepon' => 'nullable|string|max:30',
            'form.email' => 'nullable|email|max:255',
            'form.kepala_sekolah_user_id' => 'nullable|exists:users,id',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $setting = PengaturanSekolah::firstOrFail();
        $payload = $data['form'];
        $oldLogo = $setting->logo;

        if ($this->logo) {
            $payload['logo'] = $this->logo->store('logo-sekolah', 'public');
        }

        $setting->update($payload);

        if ($this->logo && $oldLogo && $oldLogo !== $setting->logo) {
            Storage::disk('public')->delete($oldLogo);
        }

        $this->reset('logo');
        session()->flash('success', 'Pengaturan sekolah berhasil diperbarui.');
    }

    public function with(): array
    {
        return [
            'setting' => PengaturanSekolah::first(),
            'kepala' => User::role('kepala_sekolah')->where('status', 'aktif')->orderBy('name')->get(),
        ];
    }
}; ?>

<div>
    <div class="mb-6">
        <p class="text-sm font-semibold text-sky-700">Manajemen</p>
        <h1 class="page-title">Pengaturan Sekolah</h1>
        <p class="page-subtitle">Kelola identitas resmi sekolah yang digunakan pada aplikasi, laporan, hasil cetak, dan dokumen PDF.</p>
    </div>

    <section class="content-hero mb-6" aria-labelledby="school-summary-title">
        <div class="relative z-10 flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex min-w-0 items-center gap-4 sm:gap-5">
                <div class="flex size-20 shrink-0 items-center justify-center sm:size-24">
                    @if($logo)
                        <img src="{{ $logo->temporaryUrl() }}" alt="Pratinjau logo sekolah baru" class="max-h-full max-w-full object-contain">
                    @elseif($setting?->logo)
                        <img src="{{ Storage::url($setting->logo) }}" alt="Logo {{ $setting->nama_sekolah }}" class="max-h-full max-w-full object-contain">
                    @else
                        <svg viewBox="0 0 24 24" class="size-14 text-sky-600" fill="none" stroke="currentColor" stroke-width="1.5" aria-label="Logo sekolah belum tersedia"><path d="m3 10 9-6 9 6-9 6-9-6Z"/><path d="M7 13v4.5c3 2 7 2 10 0V13M21 10v6"/></svg>
                    @endif
                </div>
                <div class="min-w-0">
                    <span class="inline-flex rounded-full bg-sky-100 px-3 py-1 text-xs font-bold text-sky-800">Identitas Sekolah</span>
                    <h2 id="school-summary-title" class="mt-2 text-xl font-bold tracking-tight text-slate-950 sm:text-2xl">{{ $form['nama_sekolah'] ?: 'Nama sekolah belum diisi' }}</h2>
                    <p class="mt-1 text-sm text-slate-600">NPSN: {{ $form['npsn'] ?: 'Belum diisi' }}</p>
                </div>
            </div>

            <div class="rounded-xl border border-sky-200 bg-sky-50/80 px-4 py-3 lg:max-w-md">
                <div class="flex items-start gap-3">
                    <svg viewBox="0 0 24 24" class="mt-0.5 size-5 shrink-0 text-sky-700" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 3h9l4 4v14H6V3Z"/><path d="M14 3v5h5M9 13h7M9 17h7M9 9h2"/></svg>
                    <p class="text-sm leading-6 text-sky-900">Perubahan pada halaman ini otomatis menjadi identitas utama pada header laporan, print, PDF, dan branding aplikasi.</p>
                </div>
            </div>
        </div>
    </section>

    <form wire:submit="save" class="space-y-6">
        <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1.35fr)_minmax(22rem,.65fr)]">
            <section class="card overflow-hidden" aria-labelledby="school-identity-title">
                <div class="flex items-start gap-4 border-b border-slate-100 bg-slate-50/60 px-5 py-5 sm:px-6">
                    <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-sky-100 text-sky-700">
                        <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m3 10 9-6 9 6-9 6-9-6Z"/><path d="M6 13v6h12v-6M9 19v-4h6v4"/></svg>
                    </span>
                    <div>
                        <h2 id="school-identity-title" class="text-lg font-bold text-slate-950">Identitas dan Kontak</h2>
                        <p class="mt-1 text-sm leading-6 text-slate-600">Isi data resmi sekolah agar informasi pada laporan selalu akurat.</p>
                    </div>
                </div>

                <div class="grid gap-5 p-5 sm:grid-cols-2 sm:p-6">
                    <div class="sm:col-span-2">
                        <label for="school-name" class="form-label">Nama Sekolah <span>*</span></label>
                        <input id="school-name" wire:model.live.debounce.400ms="form.nama_sekolah" class="form-input" autocomplete="organization" autofocus>
                        @error('form.nama_sekolah')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="school-npsn" class="form-label">NPSN</label>
                        <input id="school-npsn" wire:model.live.debounce.400ms="form.npsn" class="form-input tabular-nums" inputmode="numeric" autocomplete="off" placeholder="Contoh: 60100001">
                        <p class="mt-2 text-xs leading-5 text-slate-500">Nomor Pokok Sekolah Nasional.</p>
                        @error('form.npsn')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="school-phone" class="form-label">Nomor Telepon</label>
                        <input id="school-phone" type="tel" wire:model="form.telepon" class="form-input" inputmode="tel" autocomplete="tel" placeholder="Contoh: 0914 000000">
                        @error('form.telepon')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="school-email" class="form-label">Alamat Email</label>
                        <input id="school-email" type="email" wire:model="form.email" class="form-input" inputmode="email" autocomplete="email" placeholder="sekolah@contoh.sch.id">
                        @error('form.email')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="school-address" class="form-label">Alamat Lengkap</label>
                        <textarea id="school-address" wire:model="form.alamat" class="form-input min-h-32 resize-y" autocomplete="street-address" placeholder="Tuliskan alamat lengkap sekolah"></textarea>
                        <p class="mt-2 text-xs leading-5 text-slate-500">Alamat akan ditampilkan pada kop laporan dan dokumen resmi.</p>
                        @error('form.alamat')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            <div class="space-y-6">
                <section class="card overflow-hidden" aria-labelledby="school-brand-title">
                    <div class="flex items-start gap-4 border-b border-slate-100 bg-slate-50/60 px-5 py-5">
                        <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-amber-100 text-amber-700">
                            <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="m4 17 5-4 3 2 3-3 5 4"/></svg>
                        </span>
                        <div>
                            <h2 id="school-brand-title" class="text-lg font-bold text-slate-950">Logo Sekolah</h2>
                            <p class="mt-1 text-sm leading-6 text-slate-600">Gunakan logo resmi dengan latar transparan jika tersedia.</p>
                        </div>
                    </div>

                    <div class="p-5">
                        <div class="relative flex min-h-48 items-center justify-center overflow-hidden rounded-2xl border border-dashed border-slate-300 p-6">
                            <div wire:loading.flex wire:target="logo" class="absolute inset-0 z-10 items-center justify-center bg-white/85 text-sm font-semibold text-sky-700">Memproses logo...</div>
                            @if($logo)
                                <img src="{{ $logo->temporaryUrl() }}" alt="Pratinjau logo sekolah yang dipilih" class="max-h-36 max-w-full object-contain">
                            @elseif($setting?->logo)
                                <img src="{{ Storage::url($setting->logo) }}" alt="Logo sekolah saat ini" class="max-h-36 max-w-full object-contain">
                            @else
                                <div class="text-center text-slate-500">
                                    <svg viewBox="0 0 24 24" class="mx-auto size-12 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="m3 10 9-6 9 6-9 6-9-6Z"/><path d="M7 13v4.5c3 2 7 2 10 0V13"/></svg>
                                    <p class="mt-3 text-sm font-semibold">Logo belum tersedia</p>
                                </div>
                            @endif
                        </div>

                        <label for="school-logo" class="btn-secondary mt-4 w-full cursor-pointer">
                            <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 16V4M7 9l5-5 5 5M5 20h14"/></svg>
                            {{ $setting?->logo || $logo ? 'Ganti Logo' : 'Pilih Logo' }}
                        </label>
                        <input id="school-logo" type="file" wire:model="logo" accept="image/png,image/jpeg,image/webp" class="sr-only">
                        <p class="mt-3 text-center text-xs leading-5 text-slate-500">PNG, JPG, atau WebP. Ukuran maksimal 2 MB.</p>
                        @error('logo')<p class="form-error text-center" role="alert">{{ $message }}</p>@enderror
                    </div>
                </section>

                <section class="card overflow-hidden" aria-labelledby="school-leader-title">
                    <div class="flex items-start gap-4 border-b border-slate-100 bg-slate-50/60 px-5 py-5">
                        <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-emerald-100 text-emerald-700">
                            <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M5 21a7 7 0 0 1 14 0M18 5l2 2"/></svg>
                        </span>
                        <div>
                            <h2 id="school-leader-title" class="text-lg font-bold text-slate-950">Pimpinan Sekolah</h2>
                            <p class="mt-1 text-sm leading-6 text-slate-600">Pilih akun Kepala Sekolah yang aktif.</p>
                        </div>
                    </div>

                    <div class="p-5">
                        <label class="form-label">Kepala Sekolah</label>
                        <x-searchable-select model="form.kepala_sekolah_user_id" :value="$form['kepala_sekolah_user_id']??''" :options="$kepala->pluck('name','id')->all()" placeholder="Belum ditentukan" search-placeholder="Cari nama kepala sekolah..." />
                        <p class="mt-2 text-xs leading-5 text-slate-500">Nama pimpinan digunakan sebagai referensi pada informasi sekolah dan laporan.</p>
                        @error('form.kepala_sekolah_user_id')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                    </div>
                </section>
            </div>
        </div>

        <div class="card flex flex-col gap-4 p-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div class="flex items-start gap-3">
                <svg viewBox="0 0 24 24" class="mt-0.5 size-5 shrink-0 text-slate-500" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7h.01"/></svg>
                <p class="text-sm leading-6 text-slate-600">Pastikan informasi sudah benar sebelum disimpan karena data ini digunakan pada dokumen resmi sekolah.</p>
            </div>
            <button type="submit" class="btn-primary w-full shrink-0 sm:w-auto" wire:loading.attr="disabled" wire:target="save,logo">
                <svg wire:loading.remove wire:target="save" viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12.5 9.5 17 19 7.5"/></svg>
                <span wire:loading.remove wire:target="save">Simpan Pengaturan</span>
                <span wire:loading wire:target="save">Menyimpan...</span>
            </button>
        </div>
    </form>
</div>
