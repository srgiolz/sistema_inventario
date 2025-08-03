@props(['color' => 'primary', 'icon' => '', 'title' => '', 'value' => ''])

<div class="col">
    <div class="card text-white bg-{{ $color }} shadow-sm rounded-4 h-100">
        <div class="card-body p-3">
            <h6 class="card-title mb-1">
                <i class="bi {{ $icon }}"></i> {{ $title }}
            </h6>
            <p class="h5 fw-bold mb-1">{{ $value }}</p>
        </div>
    </div>
</div>
