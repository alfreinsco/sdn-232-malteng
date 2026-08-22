@props(['label'])
<section aria-label="{{ $label }}">
    <p class="sidebar-group-label">{{ $label }}</p>
    <div>{{ $slot }}</div>
</section>
