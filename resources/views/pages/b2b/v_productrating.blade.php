@extends('layouts.shop')

@section('content')
<div class="section section-scrollable" style="margin-bottom: 20px;">
    <div class="container">

        {{-- Page Title --}}
        <div class="section-title mb-4">
            <h3 class="title">{{ $page ?? 'Rate Your Delivery' }}</h3>
        </div>

        {{-- Rider Info --}}
        <p><strong>Rider:</strong> {{ $delivery->deliveryUser->name ?? 'N/A' }}</p>


        <form method="POST" action="{{ route('b2b.delivery.all.ratings.submit', $order->id) }}">
            @csrf

            {{-- Rider Rating --}}
            <div class="form-group @error('rider_rating') has-error @enderror mb-3">
                <label>Rate the delivery (1–5):</label><br>
                @for ($i = 1; $i <= 5; $i++)
                    <label class="radio-inline me-2">
                        <input type="radio" name="rider_rating" value="{{ $i }}"
                            {{ old('rider_rating', 5) == $i ? 'checked' : '' }}>
                        {{ $i }}
                    </label>
                @endfor
                @error('rider_rating')
                    <span class="help-block text-danger">{{ $message }}</span>
                @enderror
            </div>


            {{-- Rider Feedback --}}
            <div class="form-group @error('rider_feedback') has-error @enderror mb-4">
                <label for="rider_feedback">Feedback for Rider</label>
                <textarea name="rider_feedback" class="form-control" rows="3">{{ old('rider_feedback') }}</textarea>
                @error('rider_feedback')
                    <span class="help-block text-danger">{{ $message }}</span>
                @enderror
            </div>

            {{-- Product Ratings --}}
            @foreach ($order->items as $item)
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">{{ $item->product->name ?? 'Unknown Product' }}</h5>
                        <p><strong>Quantity:</strong> {{ $item->quantity }}</p>

                        {{-- Product Rating --}}
                        <div class="form-group mb-3 @error("ratings.{$item->product->id}") has-error @enderror">
                            <label>Rate this product (1–5):</label><br>
                            @for ($i = 1; $i <= 5; $i++)
                                <label class="radio-inline me-2">
                                    <input type="radio" name="ratings[{{ $item->product->id }}]" value="{{ $i }}"
                                        {{ old("ratings.{$item->product->id}", 5) == $i ? 'checked' : '' }}>
                                    {{ $i }}
                                </label>
                            @endfor
                            @error("ratings.{$item->product->id}")
                                <span class="help-block text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Product Feedback --}}
                        <div class="form-group mb-3 @error("feedbacks.{$item->product->id}") has-error @enderror">
                            <label for="feedback">Feedback</label>
                            <textarea name="feedbacks[{{ $item->product->id }}]" class="form-control" rows="3">{{ old("feedbacks.{$item->product->id}") }}</textarea>
                            @error("feedbacks.{$item->product->id}")
                                <span class="help-block text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Submit Button --}}
            <div class="d-grid mt-3">
                <button type="submit" class="btn btn-primary">Submit All Ratings</button>
            </div>
        </form>

    </div>
</div>
@endsection
