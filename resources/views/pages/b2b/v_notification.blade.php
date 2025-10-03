@extends('layouts.shop')

@section('content')

<div class="section section-scrollable" style="margin-bottom: 20px;">
    <div class="container">
        <div class="section-title">
            <h3 class="title">{{ $page }}</h3>
        </div>

            <div class="mb-3">
                <button id="markAllAsReadBtn" 
                        class="btn btn-sm" 
                        style="background-color:#6571ff; border-color:#6571ff; color:#fff;">
                    Mark Selected as Read
                </button>
            </div>


        @component('components.table', [
        'id' => 'notificationTable',
        'thead' => '
        <tr>
            <th>Message</th>
            <th>Type</th>
            <th>Time</th>
            <th>Read At</th>
            <th><input type="checkbox" id="selectAllCheckboxes"></th>
        </tr>
        '
        ])
        @endcomponent
    </div>
</div>
@endsection



@push('scripts')
<script src="{{ route('secure.js', ['filename' => 'notification']) }}"></script>
@endpush