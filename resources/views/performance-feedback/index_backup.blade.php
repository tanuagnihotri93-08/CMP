@extends('layouts.app')

@section('content')

<div class="crm-dashboard">

    <div class="hero-card">
        <p class="dashboard-eyebrow">PERFORMANCE MANAGEMENT</p>
        <h1>Add Performance Feedback</h1>
        <p class="sub-text">
            Provide feedback to team members based on their performance.
        </p>
    </div>


    <div class="crm-card">

        <form method="POST" action="{{ route('performance-feedback.store') }}">
            @csrf

            <div class="form-group">
                <label>Select User</label>

                <select name="receiver_id" class="form-control" required>
                    <option value="">Choose user</option>

                    @foreach($users as $user)
                        <option value="{{ $user->id }}">
                            {{ $user->name }}
                        </option>
                    @endforeach

                </select>
            </div>


            <div class="form-group mt-3">
                <label>Feedback</label>

                <textarea
                    name="message"
                    class="form-control"
                    rows="5"
                    placeholder="Write performance feedback..."
                    required></textarea>
            </div>


            <button type="submit" class="btn btn-primary mt-3">
                Save Feedback
            </button>

        </form>

    </div>

</div>

@endsection