<table class="table table-bordered">
    <tbody>
        <tr>
            <th width="30%">Profile</th>
            <td>
                <img src="{{ $user->profile ? asset($user->profile) : asset('assets/dashboard/images/noimage.png') }}"
                     alt="Profile" class="img-thumbnail" width="80">
            </td>
        </tr>
        <tr>
            <th>Name</th>
            <td>{{ $user->name }}</td>
        </tr>
        <tr>
            <th>Email</th>
            <td>{{ $user->email }}</td>
        </tr>
        <tr>
            <th>Username</th>
            <td>{{ $user->username }}</td>
        </tr>
        <tr>
            <th>Role</th>
            <td>{{ ucfirst($user->role) }}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td>
                @if($user->status)
                    <span class="badge bg-success">Active</span>
                @else
                    <span class="badge bg-secondary">Inactive</span>
                @endif
            </td>
        </tr>
        <tr>
            <th>Email Verified</th>
            <td>{{ $user->email_verified_at ? 'Yes' : 'No' }}</td>
        </tr>
    </tbody>
</table>


@if($user->userLog && $user->userLog->count())
<h5 class="mt-4 mb-2">User Logs</h5>
<div class="accordion" id="userLogAccordion">
    @foreach($user->userLog as $log)
    <div class="accordion-item">
        <h2 class="accordion-header" id="heading{{ $log->id }}">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapse{{ $log->id }}">
                {{ $log->event }} — {{ $log->logged_at }}
            </button>
        </h2>
        <div id="collapse{{ $log->id }}" class="accordion-collapse collapse"
             data-bs-parent="#userLogAccordion">
            <div class="accordion-body">
                <p><strong>Event:</strong> {{ $log->event }}</p>
                <p><strong>IP Address:</strong> {{ $log->ip_address }}</p>
                <p><strong>User Agent:</strong> {{ $log->user_agent }}</p>
                <p><strong>Logged At:</strong> {{ $log->logged_at }}</p>
            </div>
        </div>
    </div>
    @endforeach
</div>
@else
<p>No user logs found.</p>
@endif
