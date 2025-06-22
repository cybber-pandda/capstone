<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-3 align-items-center">
            @isset($title)
                <h6 class="card-title">{{ $title }}</h6>
            @endisset

            @if(!empty($cardtopAddButton))
                <button type="button" class="btn btn-inverse-primary btn-sm me-2" id="{{ $cardtopAddButtonId ?? 'add' }}" data-mode="{{ $cardtopButtonMode }}">
                    <i class="link-icon" data-lucide="plus"></i>
                    <span class="mt-1">{{ $cardtopAddButtonTitle ?? 'Add' }}</span>
                </button>
            @endif
        </div>

        {{ $slot }}
    </div>
</div>
