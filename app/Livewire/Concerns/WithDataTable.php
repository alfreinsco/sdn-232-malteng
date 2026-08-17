<?php

namespace App\Livewire\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

trait WithDataTable
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $sort = '';

    #[Url]
    public string $direction = '';

    #[Url(as: 'per_page')]
    public int $perPage = 10;

    #[Url(as: 'visible_columns')]
    public string $visibleColumns = '';

    public string $selectionMode = 'explicit';

    /** @var array<int, int|string> */
    public array $selectedIds = [];

    /** @var array<int, int|string> */
    public array $excludedIds = [];

    public function initializeDataTable(): void
    {
        $this->perPage = max(1, min(250, (int) $this->perPage));

        if (! in_array($this->sort, $this->tableSortableColumns(), true)) {
            $this->sort = '';
            $this->direction = '';
        }

        if (! in_array($this->direction, ['asc', 'desc'], true)) {
            $this->direction = $this->sort === '' ? '' : 'asc';
        }

        $this->visibleColumns = implode(',', $this->validatedVisibleColumns());
    }

    public function updatedSearch(): void
    {
        $this->datasetChanged();
    }

    public function updatedPerPage(mixed $value): void
    {
        $this->perPage = max(1, min(250, (int) $value));
        $this->resetPage();
    }

    public function datasetChanged(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function sortBy(string $field): void
    {
        if (! in_array($field, $this->tableSortableColumns(), true)) {
            return;
        }

        if ($this->sort !== $field) {
            $this->sort = $field;
            $this->direction = 'asc';
        } elseif ($this->direction === 'asc') {
            $this->direction = 'desc';
        } else {
            $this->sort = '';
            $this->direction = '';
        }

        $this->resetPage();
    }

    public function resetTableState(): void
    {
        $this->search = '';
        $this->sort = '';
        $this->direction = '';
        $this->perPage = 10;

        if (method_exists($this, 'resetTableFilters')) {
            $this->resetTableFilters();
        }

        $this->resetPage();
        $this->clearSelection();
    }

    public function toggleColumn(string $column): void
    {
        $hideable = collect($this->tableColumns())
            ->filter(fn (array $definition): bool => $definition['hideable'] ?? true)
            ->pluck('id')
            ->all();

        if (! in_array($column, $hideable, true)) {
            return;
        }

        $visible = $this->validatedVisibleColumns();

        if (in_array($column, $visible, true)) {
            if (count($visible) === 1) {
                return;
            }

            $visible = array_values(array_diff($visible, [$column]));
        } else {
            $ordered = collect($this->tableColumns())->pluck('id')->all();
            $visible[] = $column;
            $visible = array_values(array_intersect($ordered, $visible));
        }

        $this->visibleColumns = implode(',', $visible);
    }

    /** @return array<int, string> */
    public function validatedVisibleColumns(): array
    {
        $all = collect($this->tableColumns())->pluck('id')->all();
        $requested = array_values(array_filter(explode(',', $this->visibleColumns)));
        $valid = array_values(array_intersect($all, $requested));

        return $valid === [] ? $all : $valid;
    }

    public function toggleSelectAllDataset(): void
    {
        if ($this->selectionMode === 'all' && $this->excludedIds === []) {
            $this->clearSelection();

            return;
        }

        $this->selectionMode = 'all';
        $this->selectedIds = [];
        $this->excludedIds = [];
    }

    public function toggleRowSelection(int|string $id): void
    {
        if ($this->selectionMode === 'all') {
            $this->excludedIds = in_array($id, $this->excludedIds)
                ? array_values(array_diff($this->excludedIds, [$id]))
                : [...$this->excludedIds, $id];

            return;
        }

        $this->selectedIds = in_array($id, $this->selectedIds)
            ? array_values(array_diff($this->selectedIds, [$id]))
            : [...$this->selectedIds, $id];
    }

    public function isRowSelected(int|string $id): bool
    {
        return $this->selectionMode === 'all'
            ? ! in_array($id, $this->excludedIds)
            : in_array($id, $this->selectedIds);
    }

    public function selectedCount(int $datasetTotal): int
    {
        return $this->selectionMode === 'all'
            ? max(0, $datasetTotal - count($this->excludedIds))
            : count($this->selectedIds);
    }

    public function clearSelection(): void
    {
        $this->selectionMode = 'explicit';
        $this->selectedIds = [];
        $this->excludedIds = [];
    }

    public function applySelection(Builder $query): Builder
    {
        return $this->selectionMode === 'all'
            ? $query->when($this->excludedIds, fn (Builder $builder) => $builder->whereNotIn($builder->getModel()->getQualifiedKeyName(), $this->excludedIds))
            : $query->whereIn($query->getModel()->getQualifiedKeyName(), $this->selectedIds);
    }

    public function goToTablePage(mixed $page, int $lastPage): void
    {
        $this->gotoPage(max(1, min($lastPage, (int) $page)));
    }

    /** @return array<int, string> */
    abstract protected function tableSortableColumns(): array;

    /** @return array<int, array{id:string,label:string,sortable?:string|bool,hideable?:bool}> */
    abstract protected function tableColumns(): array;
}
