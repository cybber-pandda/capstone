@extends('layouts.dashboard')

@section('content')
<div class="page-content container-xxl">

    @include('layouts.dashboard.breadcrumb')

</div>
@endsection

@push('scripts')
<script src="{{ route('secure.js', ['filename' => 'delivery_report']) }}"></script>
@endpush