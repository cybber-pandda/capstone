@extends('layouts.shop')

@section('content')
<div class="section section-scrollable" style="margin-bottom: 20px;">
    <div class="container">

        <div class="section-title">
            <h3 class="title">{{ $page }}</h3>
        </div>

        @php
        $user = auth()->user();
        $avatar = $user->profile
        ? asset($user->profile)
        : asset('assets/avatars/' . rand(1, 17) . '.avif');
        @endphp

        <div class="row" style="margin-bottom: 20px;">
            <!-- Profile Picture -->
            <div class="col-sm-4">
                <div class="text-center">
                    <img src="{{ $avatar }}" class="avatar img-circle" alt="avatar" style="width:150px; height:150px;margin-bottom:10px;">
                    <h6>Upload a different photo</h6>
                    <form method="POST" action="{{ route('b2b.profile.upload') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="profile_picture" class="form-control">
                        <button type="submit" class="btn btn-primary btn-sm" style="margin-top:10px;">Upload</button>
                    </form>
                </div>
            </div>

            <!-- Profile Info -->
            <div class="col-sm-8">

                @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
                @endif

                <form method="POST" action="{{ route('b2b.profile.update') }}">
                    @csrf
                    @method('PUT')

                    @php
                      $name = explode(' ', $user->name)
                    @endphp

                    <!-- Firstname -->
                    <div class="form-group">
                        <label for="firstname">First Name</label>
                        <input type="text" class="form-control @error('firstname') is-invalid @enderror" name="firstname" value="{{ old('firstname', $name[0] ?? '') }}" placeholder="Enter your first name">
                        @error('firstname')
                        <span class="help-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Lastname -->
                    <div class="form-group">
                        <label for="lastname">Last Name</label>
                        <input type="text" class="form-control @error('lastname') is-invalid @enderror" name="lastname" value="{{ old('lastname', $name[1] ?? '') }}" placeholder="Enter your last name">
                        @error('lastname')
                        <span class="help-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Username -->
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" class="form-control @error('username') is-invalid @enderror" name="username" value="{{ old('username', $user->username) }}">
                        @error('username')
                        <span class="help-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label for="email">Email address</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $user->email) }}">
                        @error('email')
                        <span class="help-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <hr>

                    <h4 style="margin-bottom: 15px;">Tell me About Yourself</h4>

                    <!-- About -->
                    <div class="form-group">
                        <label for="about">About</label>
                        <textarea id="about" name="about" rows="5" class="form-control @error('about') is-invalid @enderror">{{ old('about', $user->about) }}</textarea>
                        @error('about')
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>


                    <hr>

                    <h4 style="margin-bottom: 15px;">CHANGE PASSOWRD</h4>

                    <!-- Current Password -->
                    <div class="form-group @error('current_password') has-error @enderror">
                        <label for="current_password">Current Password</label>
                        <input type="password" class="form-control @error('current_password') is-invalid @enderror" name="current_password">
                        @error('current_password')
                        <span class="help-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- New Password -->
                    <div class="form-group @error('password') has-error @enderror">
                        <label for="password">New Password</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" name="password">
                        @error('password')
                        <span class="help-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div class="form-group @error('password_confirmation') has-error @enderror">
                        <label for="password_confirmation">Confirm New Password</label>
                        <input type="password" class="form-control" name="password_confirmation">
                    </div>

                    <button type="submit" class="btn btn-success" style="margin-bottom: 45px;">Save Changes</button>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection