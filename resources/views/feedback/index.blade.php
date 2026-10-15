@extends('layouts.app')

@section('content')

<div class="crm-dashboard">

    <div class="hero-card">

        <p class="dashboard-eyebrow">
            PERFORMANCE MANAGEMENT
        </p>

        <h1>Performance Feedback</h1>

        <p class="sub-text">
            Review feedback shared between team members.
        </p>

        <a href="{{ route('performance-feedback.create') }}"
           class="btn btn-primary mt-3">
            Add Feedback
        </a>

    </div>


    <div class="crm-card">

        <h3 class="mb-4">
            Feedback History
        </h3>


        @forelse($feedbacks as $feedback)

            <div class="feedback-item mb-4">

                <div class="d-flex justify-content-between">

                    <div>

                        <strong>
                            {{ $feedback->sender->name ?? 'Unknown' }}
                        </strong>

                        <span>
                            →
                        </span>

                        <strong>
                            {{ $feedback->receiver->name ?? 'Unknown' }}
                        </strong>

                    </div>


                    <span class="badge 
                        {{ $feedback->status === 'Reviewed' 
                            ? 'bg-success' 
                            : 'bg-warning' }}">
                        {{ $feedback->status }}
                    </span>

                </div>


                <p class="mt-3">
                    {{ $feedback->message }}
                </p>


                <small>
                    {{ $feedback->created_at->format('d M Y, h:i A') }}
                </small>

            </div>


            <hr>

        @empty

            <p>
                No feedback available yet.
            </p>

        @endforelse


    </div>

</div>

@endsection