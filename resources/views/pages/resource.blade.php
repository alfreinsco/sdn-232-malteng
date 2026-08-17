<?php

use App\Models\{Guru,JamPelajaran,Kelas,MataPelajaran,Pengajaran,Semester,Siswa,TahunAjaran,User};
use App\Livewire\Concerns\WithDataTable;
use App\Services\{AktivasiSemester,AktivasiTahunAjaran,UbahStatusMasterData};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

new class extends Component {
    use WithDataTable;
    public string $resource=''; public bool $showForm=false; public ?int $editingId=null; public array $form=[];
    #[\Livewire\Attributes\Url] public string $status='';
    public function mount(?string $resource=null): void { $this->resource=$resource??request()->route('resource'); abort_unless(isset($this->definitions()[$this->resource]),404);$this->initializeDataTable(); }
    public function updatedStatus(): void{$this->datasetChanged();}
    public function resetTableFilters():void{$this->status='';}
    private function definitions(): array { return [
        'tahun-ajaran'=>['label'=>'Tahun Ajaran','model'=>TahunAjaran::class,'permission'=>'tahun-ajaran.manage','search'=>['nama'],'sort'=>['nama','tanggal_mulai','status'],'fields'=>[
            'nama'=>['label'=>'Nama','type'=>'text','required'=>true],'tanggal_mulai'=>['label'=>'Tanggal Mulai','type'=>'date','required'=>true],'tanggal_selesai'=>['label'=>'Tanggal Selesai','type'=>'date','required'=>true],'status'=>['label'=>'Status','type'=>'status','required'=>true]]],
        'semester'=>['label'=>'Semester','model'=>Semester::class,'permission'=>'semester.manage','search'=>['nama'],'with'=>['tahunAjaran'],'sort'=>['nama','tanggal_mulai','status'],'fields'=>[
            'tahun_ajaran_id'=>['label'=>'Tahun Ajaran','type'=>'select','options'=>'tahun'],'nama'=>['label'=>'Semester','type'=>'select','values'=>['ganjil'=>'Ganjil','genap'=>'Genap']],'tanggal_mulai'=>['label'=>'Tanggal Mulai','type'=>'date'],'tanggal_selesai'=>['label'=>'Tanggal Selesai','type'=>'date'],'status'=>['label'=>'Status','type'=>'status']]],
        'guru'=>['label'=>'Guru','model'=>Guru::class,'permission'=>'guru.create','search'=>['nama_lengkap','nip','nuptk'],'sort'=>['nama_lengkap','nip','status'],'fields'=>[
            'user_id'=>['label'=>'Akun Pengguna','type'=>'select','optional'=>true,'options'=>'users'],'nama_lengkap'=>['label'=>'Nama Lengkap','type'=>'text'],'nip'=>['label'=>'NIP','type'=>'text','optional'=>true],'nuptk'=>['label'=>'NUPTK','type'=>'text','optional'=>true],'jenis_kelamin'=>['label'=>'Jenis Kelamin','type'=>'select','optional'=>true,'values'=>['laki-laki'=>'Laki-laki','perempuan'=>'Perempuan']],'tempat_lahir'=>['label'=>'Tempat Lahir','type'=>'text','optional'=>true],'tanggal_lahir'=>['label'=>'Tanggal Lahir','type'=>'date','optional'=>true],'telepon'=>['label'=>'Telepon','type'=>'text','optional'=>true],'alamat'=>['label'=>'Alamat','type'=>'textarea','optional'=>true],'status'=>['label'=>'Status','type'=>'status']]],
        'siswa'=>['label'=>'Siswa','model'=>Siswa::class,'permission'=>'siswa.create','search'=>['nama_lengkap','nis','nisn'],'sort'=>['nama_lengkap','nis','nisn','jenis_kelamin','tanggal_lahir','status'],'columns'=>['nama_lengkap','nis','nisn','jenis_kelamin','tempat_tanggal_lahir','status'],'columnSort'=>['tempat_tanggal_lahir'=>'tanggal_lahir'],'columnLabels'=>['tempat_tanggal_lahir'=>'Tempat, Tanggal Lahir'],'fields'=>[
            'user_id'=>['label'=>'Akun Pengguna','type'=>'select','optional'=>true,'options'=>'users'],'nama_lengkap'=>['label'=>'Nama Lengkap','type'=>'text'],'nis'=>['label'=>'NIS','type'=>'text','optional'=>true],'nisn'=>['label'=>'NISN','type'=>'text','optional'=>true],'jenis_kelamin'=>['label'=>'Jenis Kelamin','type'=>'select','optional'=>true,'values'=>['laki-laki'=>'Laki-laki','perempuan'=>'Perempuan']],'tempat_lahir'=>['label'=>'Tempat Lahir','type'=>'text','optional'=>true],'tanggal_lahir'=>['label'=>'Tanggal Lahir','type'=>'date','optional'=>true],'alamat'=>['label'=>'Alamat','type'=>'textarea','optional'=>true],'status'=>['label'=>'Status','type'=>'status']]],
        'kelas'=>['label'=>'Kelas','model'=>Kelas::class,'permission'=>'kelas.create','search'=>['nama'],'with'=>['tahunAjaran','waliKelas'],'sort'=>['nama','tingkat','status'],'fields'=>[
            'tahun_ajaran_id'=>['label'=>'Tahun Ajaran','type'=>'select','options'=>'tahun'],'nama'=>['label'=>'Nama Kelas','type'=>'text'],'tingkat'=>['label'=>'Tingkat','type'=>'number'],'wali_kelas_id'=>['label'=>'Wali Kelas','type'=>'select','optional'=>true,'options'=>'guru'],'status'=>['label'=>'Status','type'=>'status']]],
        'mata-pelajaran'=>['label'=>'Mata Pelajaran','model'=>MataPelajaran::class,'permission'=>'mata-pelajaran.create','search'=>['nama','kode'],'sort'=>['nama','kode','status'],'fields'=>['kode'=>['label'=>'Kode','type'=>'text','optional'=>true],'nama'=>['label'=>'Nama','type'=>'text'],'status'=>['label'=>'Status','type'=>'status']]],
        'jam-pelajaran'=>['label'=>'Jam Pelajaran','model'=>JamPelajaran::class,'permission'=>'jam-pelajaran.manage','search'=>['nama'],'sort'=>['urutan','nama','jam_mulai','jam_selesai','jenis','status'],'fields'=>['nama'=>['label'=>'Nama','type'=>'text'],'jam_mulai'=>['label'=>'Jam Mulai','type'=>'time'],'jam_selesai'=>['label'=>'Jam Selesai','type'=>'time'],'urutan'=>['label'=>'Urutan','type'=>'number'],'jenis'=>['label'=>'Jenis','type'=>'select','values'=>['pelajaran'=>'Pelajaran','istirahat'=>'Istirahat']],'status'=>['label'=>'Status','type'=>'status']]],
        'pengajaran'=>['label'=>'Pengajaran','model'=>Pengajaran::class,'permission'=>'pengajaran.manage','search'=>[],'with'=>['semester.tahunAjaran','kelas','mataPelajaran','guru'],'sort'=>['id','status'],'fields'=>['semester_id'=>['label'=>'Semester','type'=>'select','options'=>'semester'],'kelas_id'=>['label'=>'Kelas','type'=>'select','options'=>'kelas'],'mata_pelajaran_id'=>['label'=>'Mata Pelajaran','type'=>'select','options'=>'mapel'],'guru_id'=>['label'=>'Guru','type'=>'select','options'=>'guru'],'status'=>['label'=>'Status','type'=>'status']]],
        'pengguna'=>['label'=>'Pengguna','model'=>User::class,'permission'=>'users.create','search'=>['name','username','email'],'with'=>['roles'],'sort'=>['name','username','status'],'fields'=>['name'=>['label'=>'Nama','type'=>'text'],'username'=>['label'=>'Username','type'=>'text'],'email'=>['label'=>'Email','type'=>'email','optional'=>true],'role'=>['label'=>'Role','type'=>'select','values'=>['admin'=>'Admin','guru'=>'Guru','siswa'=>'Siswa','kepala_sekolah'=>'Kepala Sekolah']],'password'=>['label'=>'Password','type'=>'password','optionalEdit'=>true],'status'=>['label'=>'Status','type'=>'status']]],
    ]; }
    public function definition(): array{return $this->definitions()[$this->resource];}
    protected function tableSortableColumns():array{return $this->definition()['sort'];}
    protected function tableColumns():array{$definition=$this->definition();$columns=$definition['columns']??$definition['sort'];return collect($columns)->map(function(string $column)use($definition):array{$sortField=$definition['columnSort'][$column]??$column;return ['id'=>$column,'label'=>$definition['columnLabels'][$column]??$definition['fields'][$column]['label']??str($column)->replace('_',' ')->title(),'sortable'=>in_array($sortField,$definition['sort'],true)?$sortField:false,'hideable'=>$column!=='status'];})->all();}
    public function options(string $name): array { return match($name){'tahun'=>TahunAjaran::orderByDesc('nama')->pluck('nama','id')->all(),'guru'=>Guru::where('status','aktif')->orderBy('nama_lengkap')->pluck('nama_lengkap','id')->all(),'kelas'=>Kelas::with('tahunAjaran')->where('status','aktif')->get()->mapWithKeys(fn($x)=>[$x->id=>$x->nama.' · '.$x->tahunAjaran->nama])->all(),'mapel'=>MataPelajaran::where('status','aktif')->orderBy('nama')->pluck('nama','id')->all(),'semester'=>Semester::with('tahunAjaran')->get()->mapWithKeys(fn($x)=>[$x->id=>$x->tahunAjaran->nama.' · '.ucfirst($x->nama)])->all(),'users'=>User::where('status','aktif')->where(function($q){$q->whereDoesntHave('guru')->whereDoesntHave('siswa');if($this->editingId){$current=$this->definition()['model']::find($this->editingId);if($current?->user_id)$q->orWhere('id',$current->user_id);}})->orderBy('name')->pluck('name','id')->all(),default=>[]}; }
    public function openCreate(): void { abort_unless(auth()->user()->can($this->definition()['permission']),403); $this->resetValidation();$this->editingId=null;$this->form=[];foreach($this->definition()['fields'] as $key=>$field)$this->form[$key]=$key==='status'?'aktif':'';$this->showForm=true; }
    public function edit(int $id): void { abort_unless(auth()->user()->can(str_replace('create','update',$this->definition()['permission']))||auth()->user()->can($this->definition()['permission']),403);$item=$this->definition()['model']::findOrFail($id);$this->editingId=$id;$this->form=$item->only(array_keys($this->definition()['fields']));if($this->resource==='pengguna'){$this->form['role']=$item->getRoleNames()->first();$this->form['password']='';}$this->showForm=true; }
    private function rules(): array { $rules=[];foreach($this->definition()['fields'] as $key=>$field){$r=[];$optional=($field['optional']??false)||(($field['optionalEdit']??false)&&$this->editingId);$r[]=$optional?'nullable':'required';$r[]=match(true){$field['type']==='select'&&isset($field['options'])=>'integer',$field['type']==='number'=>'integer',$field['type']==='date'=>'date',$field['type']==='email'=>'email',$field['type']==='password'=>'string|min:8',$field['type']==='time'=>'date_format:H:i',default=>'string'};if(isset($field['values']))$r[]=Rule::in(array_keys($field['values']));if($field['type']==='status')$r[]=Rule::in(['aktif','nonaktif']);$rules['form.'.$key]=$r;}
        $model=$this->definition()['model']; foreach(['username','email','nip','nuptk','nis','nisn','kode','user_id','urutan'] as $unique)if(array_key_exists($unique,$this->definition()['fields']))$rules['form.'.$unique][]=Rule::unique((new $model)->getTable(),$unique)->ignore($this->editingId);if(array_key_exists('user_id',$this->definition()['fields']))$rules['form.user_id'][]='exists:users,id';
        foreach(['tahun_ajaran_id'=>'tahun_ajaran','wali_kelas_id'=>'guru','semester_id'=>'semester','kelas_id'=>'kelas','mata_pelajaran_id'=>'mata_pelajaran','guru_id'=>'guru'] as $field=>$table)if(array_key_exists($field,$this->definition()['fields']))$rules['form.'.$field][]='exists:'.$table.',id';
        if($this->resource==='tahun-ajaran'){$rules['form.nama'][]='regex:/^\d{4}\/\d{4}$/';$rules['form.tanggal_selesai'][]='after_or_equal:form.tanggal_mulai';}
        if($this->resource==='semester')$rules['form.tanggal_selesai'][]='after_or_equal:form.tanggal_mulai'; if($this->resource==='jam-pelajaran'){$rules['form.jam_selesai'][]='after:form.jam_mulai';$rules['form.urutan'][]='min:1';} return $rules; }
    private function messages(): array { return ['form.urutan.unique'=>'Urutan tersebut sudah digunakan oleh jam pelajaran lain.','form.urutan.min'=>'Urutan minimal bernilai 1.','form.jam_selesai.after'=>'Jam selesai harus lebih akhir dari jam mulai.']; }
    public function save(): void { abort_unless(auth()->user()->can($this->definition()['permission'])||($this->editingId&&auth()->user()->can(str_replace('create','update',$this->definition()['permission']))),403);$this->validate($this->rules(),$this->messages());$data=$this->form;foreach($this->definition()['fields'] as $key=>$field)if(($field['optional']??false)&&($data[$key]??null)==='')$data[$key]=null;if($this->resource==='tahun-ajaran'){[$awal,$akhir]=array_map('intval',explode('/',$data['nama']));if($akhir!==$awal+1)throw ValidationException::withMessages(['form.nama'=>'Tahun akhir harus satu tahun setelah tahun awal.']);}if($this->resource==='semester'&&Semester::where('tahun_ajaran_id',$data['tahun_ajaran_id'])->where('nama',$data['nama'])->whereKeyNot($this->editingId??0)->exists())throw ValidationException::withMessages(['form.nama'=>'Semester tersebut sudah tersedia pada tahun ajaran ini.']);if($this->resource==='kelas'&&Kelas::where('tahun_ajaran_id',$data['tahun_ajaran_id'])->where('nama',$data['nama'])->whereKeyNot($this->editingId??0)->exists())throw ValidationException::withMessages(['form.nama'=>'Nama kelas sudah tersedia pada tahun ajaran ini.']);if($this->resource==='pengajaran'&&Pengajaran::where('semester_id',$data['semester_id'])->where('kelas_id',$data['kelas_id'])->where('mata_pelajaran_id',$data['mata_pelajaran_id'])->where('guru_id',$data['guru_id'])->whereKeyNot($this->editingId??0)->exists())throw ValidationException::withMessages(['form.guru_id'=>'Pengajaran yang sama sudah tersedia.']);
        if($this->resource==='pengguna'){$role=$data['role'];unset($data['role']);if(empty($data['password']))unset($data['password']);$item=User::updateOrCreate(['id'=>$this->editingId],$data);$item->syncRoles([$role]);}
        else{$aktifkanTahun=$this->resource==='tahun-ajaran'&&($data['status']??null)==='aktif';$aktifkanSemester=$this->resource==='semester'&&($data['status']??null)==='aktif';if($aktifkanTahun||$aktifkanSemester)$data['status']='nonaktif';if(in_array($this->resource,['guru','siswa'])&&!empty($data['user_id'])){$other=$this->resource==='guru'?Siswa::class:Guru::class;if($other::where('user_id',$data['user_id'])->exists())throw ValidationException::withMessages(['form.user_id'=>'Akun sudah terhubung dengan profil lain.']);}try{$item=$this->definition()['model']::updateOrCreate(['id'=>$this->editingId],$data);}catch(UniqueConstraintViolationException $exception){if($this->resource==='jam-pelajaran')throw ValidationException::withMessages(['form.urutan'=>'Urutan tersebut sudah digunakan oleh jam pelajaran lain.']);throw $exception;}if($item instanceof Guru&&$item->user)$item->user->syncRoles(['guru']);if($item instanceof Siswa&&$item->user)$item->user->syncRoles(['siswa']);if($aktifkanTahun)app(AktivasiTahunAjaran::class)->handle($item);if($aktifkanSemester)app(AktivasiSemester::class)->handle($item);}
        $this->showForm=false;$this->dispatch('saved');session()->flash('success','Data berhasil disimpan.'); }
    public function toggleStatus(int $id): void { abort_unless(auth()->user()->hasRole('admin'),403);$item=$this->definition()['model']::findOrFail($id);if($this->resource==='pengguna'&&$item->is(auth()->user())&&$item->status==='aktif'){$this->dispatch('notify',type:'error',message:'Akun yang sedang digunakan tidak dapat dinonaktifkan.');return;}$status=$item->status==='aktif'?'nonaktif':'aktif';try{app(UbahStatusMasterData::class)->handle($item,$status);}catch(ValidationException $exception){$this->dispatch('notify',type:'error',message:collect($exception->errors())->flatten()->first()??'Status data tidak dapat diubah.');return;}$this->dispatch('notify',type:'success',message:$status==='aktif'?'Data berhasil diaktifkan.':'Data berhasil dinonaktifkan.'); }
    private function tableQuery(){ $d=$this->definition();$model=$d['model'];$query=$model::query()->with($d['with']??[])->when($this->search,function($q){$fields=$this->definition()['search'];if($fields)$q->where(function($x)use($fields){foreach($fields as $i=>$field)$i?$x->orWhere($field,'like','%'.$this->search.'%'):$x->where($field,'like','%'.$this->search.'%');});})->when($this->status,fn($q)=>$q->where('status',$this->status));if($this->resource==='pengajaran'&&auth()->user()->hasRole('guru'))$query->where('guru_id',auth()->user()->guru?->id);return $query; }
    public function bulkSetStatus(string $status):void{abort_unless(auth()->user()->hasRole('admin'),403);if(!in_array($status,['aktif','nonaktif'],true))return;$query=$this->applySelection($this->tableQuery());$count=(clone $query)->count();if($count===0)return;if($status==='aktif'&&in_array($this->resource,['tahun-ajaran','semester'],true)&&$count>1){$this->dispatch('notify',type:'error',message:'Tahun ajaran atau semester aktif harus dipilih satu per satu.');return;}$skippedCurrentUser=false;$query->orderBy((new ($this->definition()['model']))->getKeyName())->each(function(Model $item)use($status,&$skippedCurrentUser):void{if($this->resource==='pengguna'&&$item->is(auth()->user())&&$status==='nonaktif'){$skippedCurrentUser=true;return;}app(UbahStatusMasterData::class)->handle($item,$status);});$this->clearSelection();$message=$status==='aktif'?'Data terpilih berhasil diaktifkan.':'Data terpilih berhasil dinonaktifkan.';if($skippedCurrentUser)$message.=' Akun yang sedang digunakan dilewati.';$this->dispatch('notify',type:'success',message:$message);}
    public function with(): array { $d=$this->definition();$error=null;try{$query=$this->tableQuery();$total=(clone $query)->count();$items=$this->sort!==''?$query->orderBy($this->sort,$this->direction)->paginate($this->perPage):$query->orderByDesc((new $d['model'])->getKeyName())->paginate($this->perPage);}catch(\Throwable $exception){report($exception);$error='Terjadi kesalahan saat mengambil data.';$total=0;$items=$d['model']::query()->whereRaw('1=0')->paginate($this->perPage);}return ['items'=>$items,'definition'=>$d,'tableColumns'=>$this->tableColumns(),'visibleColumnIds'=>$this->validatedVisibleColumns(),'datasetTotal'=>$total,'tableError'=>$error]; }
};
?>
<div>
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"><div><p class="text-sm font-semibold text-sky-700">Master Data</p><h1 class="page-title">{{ $definition['label'] }}</h1><p class="page-subtitle">Kelola data {{ strtolower($definition['label']) }} secara aman dan terstruktur.</p></div>@can($definition['permission'])<button wire:click="openCreate" class="btn-primary">Tambah {{ $definition['label'] }}</button>@endcan</div>
<div class="mb-4 grid gap-3 lg:grid-cols-[minmax(18rem,1fr)_auto]">
    <div class="card p-4">
        <label class="form-label" for="global-search">Pencarian Utama</label>
        <div class="relative">
            <svg viewBox="0 0 24 24" class="pointer-events-none absolute left-3 top-3 size-5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
            <input id="global-search" type="search" wire:model.live.debounce.400ms="search" class="form-input pl-10" placeholder="Cari {{ strtolower($definition['label']) }}...">
        </div>
    </div>
    <div class="card flex flex-wrap items-end gap-3 p-4">
        <div class="min-w-44 flex-1"><label class="form-label">Filter Status</label><x-searchable-select model="status" :value="$status" :options="['aktif'=>'Aktif','nonaktif'=>'Nonaktif']" placeholder="Semua status" search-placeholder="Cari status..." /></div>
        <button type="button" wire:click="resetTableState" class="btn-secondary">Reset Filter</button>
        <x-data-table.column-toggle :columns="$tableColumns" :visible="$visibleColumnIds" />
    </div>
</div>

<x-data-table.bulk-toolbar :count="$this->selectedCount($datasetTotal)">
    @if(!in_array($resource,['tahun-ajaran','semester'],true))<button type="button" wire:click="bulkSetStatus('aktif')" class="btn-secondary text-emerald-700">Aktifkan</button>@endif
    <button type="button" wire:click="bulkSetStatus('nonaktif')" wire:confirm="Nonaktifkan {{ $this->selectedCount($datasetTotal) }} data terpilih? Data tidak akan dihapus." class="btn-secondary text-rose-700">Nonaktifkan</button>
</x-data-table.bulk-toolbar>

<div class="table-shell">
    <div class="table-scroll">
        <table class="data-table">
            <thead><tr>
                <th class="table-select-cell sticky left-0 z-20 w-14" aria-label="Pilih semua data">
                    <input type="checkbox" class="size-4 rounded border-slate-300 text-sky-600"
                        wire:click="toggleSelectAllDataset"
                        @checked($datasetTotal > 0 && $this->selectedCount($datasetTotal) === $datasetTotal)
                        x-data x-effect="$el.indeterminate = {{ $this->selectedCount($datasetTotal) > 0 && $this->selectedCount($datasetTotal) < $datasetTotal ? 'true' : 'false' }}"
                        aria-label="Pilih atau batalkan seluruh data hasil pencarian dan filter">
                </th>
                @foreach($tableColumns as $column)
                    @continue(!in_array($column['id'],$visibleColumnIds,true))
                    <th>
                        @if($column['sortable'])
                            <button type="button" wire:click="sortBy('{{ $column['sortable'] }}')" class="inline-flex min-h-11 items-center gap-1 text-left" aria-label="Urutkan berdasarkan {{ $column['label'] }}">
                                <span>{{ $column['label'] }}</span>
                                <span aria-hidden="true">{{ $sort===$column['sortable']?($direction==='asc'?'↑':'↓'):'↕' }}</span>
                            </button>
                        @else<span class="inline-flex min-h-11 items-center">{{ $column['label'] }}</span>@endif
                    </th>
                @endforeach
                <th class="table-action-cell sticky right-0 z-20">Aksi</th>
            </tr></thead>
            <tbody>
                @foreach($items as $item)
                    @php $selected=$this->isRowSelected($item->getKey()); @endphp
                    <tr class="{{ $selected?'is-selected':'' }}" wire:key="{{ $resource }}-row-{{ $item->getKey() }}" wire:loading.remove>
                        <td class="table-select-cell sticky left-0 z-10">
                            <input type="checkbox" class="size-4 rounded border-slate-300 text-sky-600" @checked($selected)
                                wire:click="toggleRowSelection({{ $item->getKey() }})" aria-label="Pilih {{ data_get($item,'nama_lengkap')??data_get($item,'nama')??data_get($item,'name')??'data' }}">
                        </td>
                        @foreach($tableColumns as $column)
                            @continue(!in_array($column['id'],$visibleColumnIds,true))
                            @php $value=data_get($item,$column['id']); @endphp
                            <td>
                                @if($column['id']==='status')<span class="{{ $value==='aktif'?'badge-active':'badge-inactive' }}">{{ ucfirst($value) }}</span>
                                @elseif(in_array($column['id'],['jam_mulai','jam_selesai'],true)){{ substr((string)$value,0,5) }}
                                @elseif($column['id']==='jenis'||$column['id']==='jenis_kelamin'){{ ucfirst((string)$value)?:'-' }}
                                @elseif($column['id']==='tempat_tanggal_lahir'){{ collect([$item->tempat_lahir,$item->tanggal_lahir?->translatedFormat('d F Y')])->filter()->implode(', ')?:'-' }}
                                @else{{ filled($value)?$value:'-' }}@endif
                            </td>
                        @endforeach
                        <td class="table-action-cell sticky right-0 z-10"><div class="flex gap-2">@can($definition['permission'])<button wire:click="edit({{ $item->id }})" class="btn-secondary">Ubah</button><button wire:click="toggleStatus({{ $item->id }})" wire:confirm="{{ $item->status==='aktif'?'Nonaktifkan data ini? Data tidak akan dihapus.':'Aktifkan kembali data ini?' }}" class="btn-secondary {{ $item->status==='aktif'?'text-rose-700':'text-emerald-700' }}">{{ $item->status==='aktif'?'Nonaktifkan':'Aktifkan' }}</button>@endcan</div></td>
                    </tr>
                @endforeach
                <x-data-table.states :columns="count($visibleColumnIds)+2" :empty="$items->isEmpty()" :filtered="filled($search)||filled($status)" :error="$tableError" />
            </tbody>
        </table>
    </div>
    <x-data-table.pagination :paginator="$items" :per-page="$perPage" />
</div>
@if($showForm)<div class="fixed inset-0 z-[80] grid place-items-center overflow-y-auto bg-slate-950/50 p-4" role="dialog" aria-modal="true"><form wire:submit="save" class="card w-full max-w-2xl"><div class="flex items-center justify-between border-b border-slate-100 p-5"><h2 class="text-lg font-semibold">{{ $editingId?'Ubah':'Tambah' }} {{ $definition['label'] }}</h2><button type="button" wire:click="$set('showForm',false)" class="grid size-11 place-items-center rounded-lg hover:bg-slate-100" aria-label="Tutup">&times;</button></div><div class="grid max-h-[65vh] gap-5 overflow-y-auto p-5 sm:grid-cols-2">
@foreach($definition['fields'] as $key=>$field)<div class="{{ ($field['type']==='textarea')?'sm:col-span-2':'' }}"><label class="form-label" for="field-{{ $key }}">{{ $field['label'] }} @unless($field['optional']??false)<span>*</span>@endunless</label>
@if($field['type']==='select'||$field['type']==='status')<select id="field-{{ $key }}" wire:model="form.{{ $key }}" class="form-input"><option value="">Pilih...</option>@php $opts=$field['type']==='status'?['aktif'=>'Aktif','nonaktif'=>'Nonaktif']:($field['values']??$this->options($field['options']??'')); @endphp @foreach($opts as $v=>$l)<option value="{{ $v }}">{{ $l }}</option>@endforeach</select>
@elseif($field['type']==='textarea')<textarea id="field-{{ $key }}" wire:model="form.{{ $key }}" class="form-input min-h-24"></textarea>@else<input id="field-{{ $key }}" type="{{ $field['type'] }}" wire:model="form.{{ $key }}" class="form-input">@endif @error('form.'.$key)<p class="form-error" role="alert">{{ $message }}</p>@enderror</div>@endforeach
</div><div class="flex justify-end gap-3 border-t border-slate-100 p-5"><button type="button" wire:click="$set('showForm',false)" class="btn-secondary">Batal</button><button type="submit" wire:loading.attr="disabled" class="btn-primary"><span wire:loading.remove>Simpan</span><span wire:loading>Menyimpan...</span></button></div></form></div>@endif
</div>
