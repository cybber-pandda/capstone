@extends('layouts.dashboard')

@section('content')
    <div class="page-content container-xxl">


        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">

                @component('components.card', [
                    'title' => 'Notification List',
                    'cardtopAddButton' => false,
                ])

                <div class="mb-3">
                    <button id="markAllAsReadBtn" class="btn btn-success btn-sm">
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

                @endcomponent
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script src="{{ route('secure.js', ['filename' => 'notification']) }}"></script>
@endpush