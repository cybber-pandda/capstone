<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-3 align-items-center">
            @isset($title)
                <h6 class="card-title">{{ $title }}</h6>
            @endisset
            
            <div>
            @if(!empty($cardtopAddButton))
                @if($cardtopAddButtonTitle === 'Send Email Order')
                 <a href="{{ url('/customer-email-order?type=walkin') }}" class="btn btn-inverse-dark btn-sm mx-1 mb-1">Walk-In Order</a>
                @endif
                <button type="button" class="btn btn-inverse-primary btn-sm me-2 mb-1" id="{{ $cardtopAddButtonId ?? 'add' }}" data-mode="{{ $cardtopButtonMode }}">
                    <i class="link-icon" data-lucide="plus"></i>
                    <span class="mt-1">{{ $cardtopAddButtonTitle ?? 'Add' }}</span>
                </button>
            @endif
            </div>
        </div>

        {{ $slot }}
    </div>
</div>
