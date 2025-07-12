@extends('layouts.shop')

@section('content')
<div class="section section-scrollable" style="margin-bottom: 20px;">
    <div class="container">

        <div class="section-title">
            <h3 class="title">{{ $page }}</h3>
        </div>

        <p><strong>Rider:</strong> {{ $delivery->deliveryUser->name ?? 'N/A' }}</p>

        <form method="POST" action="{{ route('b2b.delivery.rider.rate.submit', $delivery->id) }}">
            @csrf

            <div class="form-group @error('rating') has-error @enderror">
                <label>Rate the delivery (1–5):</label><br>
                @for ($i = 1; $i <= 5; $i++)
                    <label class="radio-inline">
                    <input type="radio" name="rating" value="{{ $i }}" {{ old('rating') == $i ? 'checked' : '' }}> {{ $i }}
                    </label>
                    @endfor
                    @error('rating')
                    <span class="help-block">{{ $message }}</span>
                    @enderror
            </div>

            <div class="form-group @error('feedback') has-error @enderror">
                <label for="feedback">Feedback</label>
                <textarea name="feedback" class="form-control" rows="3">{{ old('feedback') }}</textarea>
                @error('feedback')
                <span class="help-block">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">Submit Rating</button>
        </form>

    </div>
</div>
@endsection