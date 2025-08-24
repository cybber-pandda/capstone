@extends('layouts.shop')

@section('content')
<div class="section section-scrollable" style="margin-bottom: 20px;">
    <div class="container">

        <div class="section-title">
            <h3 class="title">{{ $page }}</h3>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissable" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">&times;</button>
                {{ session('success') }}
            </div>
        @endif

        @foreach ($order->items as $item)
            <div class="card" style="margin-bottom:15px;">
                <div class="card-body">
                    <h5 class="card-title">{{ $item->product->name ?? 'Unknown Product' }}</h5>
                    <p><strong>Quantity:</strong> {{ $item->quantity }}</p>

                    <form method="POST" action="{{ route('b2b.delivery.product.rate.submit', $item->product->id) }}">
                        @csrf

                        <div class="form-group mb-3 @error('rating') has-error @enderror">
                            <label>Rate this product (1–5):</label><br>
                            @for ($i = 1; $i <= 5; $i++)
                                <label class="radio-inline me-2">
                                    <input type="radio" name="rating" value="{{ $i }}" {{ old('rating') == $i ? 'checked' : '' }}> {{ $i }}
                                </label>
                            @endfor
                            @error('rating')
                                <span class="help-block text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-3 @error('feedback') has-error @enderror">
                            <label for="feedback">Feedback</label>
                            <textarea name="feedback" class="form-control" rows="3">{{ old('feedback') }}</textarea>
                            @error('feedback')
                                <span class="help-block text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">Submit Rating</button>
                    </form>
                </div>
            </div>
        @endforeach

    </div>
</div>
@endsection
