@extends('layouts.app')

@section('content')

<div class="crm-dashboard crm-feedback-page">

    <div class="hero-card feedback-hero">
        <div>
            <p class="dashboard-eyebrow">PERFORMANCE MANAGEMENT</p>
            <h1>Performance Feedback</h1>
            <p class="sub-text">Review feedback history and team improvement notes.</p>
        </div>

        @if(auth()->user()?->hasPermission('performance.feedback.create'))
            <a href="{{ route('performance-feedback.create') }}" class="fb-btn fb-btn-lg"><i class="bi bi-plus-lg" aria-hidden="true"></i> Add feedback</a>
        @endif
    </div>

    @if(session('success'))
        <div class="fb-alert">{{ session('success') }}</div>
    @endif

    <section class="fb-card" aria-label="Performance feedback history">
        <div class="fb-table-wrap">
        <table class="fb-table">
            <thead>
                <tr>
                    <th>From</th>
                    <th>To</th>
                    <th>Feedback</th>
                    <th>Rating</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($feedbacks as $feedback)
                    <tr>
                        <td>{{ $feedback->sender?->name ?? 'Unknown' }}</td>
                        <td>{{ $feedback->receiver?->name ?? 'Unknown' }}</td>
                        <td class="fb-msg">{{ $feedback->message }}</td>
                        <td class="fb-stars">
                            @if($feedback->rating)
                                @for($i = 1; $i <= 5; $i++)
                                    <span class="{{ $i <= $feedback->rating ? 'on' : '' }}" aria-hidden="true">&#9733;</span>
                                @endfor
                            @else
                                <span class="fb-none">N/A</span>
                            @endif
                        </td>
                        <td>
                            <span @class(['fb-badge', 'open' => $feedback->status === 'Open', 'reviewed' => $feedback->status === 'Reviewed'])>{{ $feedback->status }}</span>
                        </td>
                        <td>{{ $feedback->created_at?->format('d M Y') }}</td>
                        <td>
                            @if($feedback->status === 'Open')
                                <form method="POST" action="{{ route('performance-feedback.status', $feedback) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="Reviewed">
                                    <button type="submit" class="fb-btn"><i class="bi bi-check2" aria-hidden="true"></i> Mark reviewed</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="fb-empty">
                            <div class="fb-empty-state"><span class="fb-empty-icon"><i class="bi bi-chat-square-text" aria-hidden="true"></i></span><strong>No feedback yet</strong><span>Feedback shared with your team will appear here.</span></div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </section>

</div>

<style>
.feedback-hero {
    display: flex; justify-content: space-between; align-items: center; gap: 20px;
    padding: 36px 42px; margin-bottom: 28px; border-radius: 28px;
    background: linear-gradient(135deg, rgba(37,99,235,.18), rgba(124,58,237,.18));
    border: 1px solid rgba(255,255,255,.12);
}
.feedback-hero h1 { margin: 8px 0 10px; color: #fff; }
.fb-alert {
    margin-bottom: 20px; padding: 14px 18px; border-radius: 14px;
    background: rgba(34,197,94,.15); border: 1px solid rgba(34,197,94,.35); color: #86efac;
}
.fb-card {
    padding: 24px; border-radius: 24px; overflow-x: auto;
    background: linear-gradient(145deg, rgba(255,255,255,.10), rgba(255,255,255,.03));
    border: 1px solid rgba(255,255,255,.12);
}
.fb-table { width: 100%; border-collapse: separate; border-spacing: 0 10px; }
.fb-table th { padding: 12px 16px; text-align: left; color: #94a3b8; font-size: 13px; }
.fb-table td { padding: 16px; background: rgba(255,255,255,.05); color: #e2e8f0; }
.fb-table tr td:first-child { border-radius: 14px 0 0 14px; }
.fb-table tr td:last-child { border-radius: 0 14px 14px 0; }
.fb-msg { max-width: 360px; }
.fb-empty { text-align: center; color: #94a3b8; }
.fb-badge {
    display: inline-block; padding: 5px 12px; border-radius: 999px; font-size: 12px; font-weight: 600;
    background: rgba(250,204,21,.18); color: #fde68a;
}
.fb-badge.reviewed { background: rgba(34,197,94,.18); color: #86efac; }
.fb-btn {
    display: inline-block; padding: 8px 14px; border: 0; border-radius: 10px; cursor: pointer;
    color: #fff; font-size: 12px; text-decoration: none;
    background: linear-gradient(135deg, #2563eb, #7c3aed);
}
.fb-btn-lg { padding: 12px 26px; font-size: 14px; font-weight: 600; border-radius: 12px; }
.fb-stars { white-space: nowrap; font-size: 18px; letter-spacing: 2px; }
.fb-stars span { color: rgba(255,255,255,.2); }
.fb-stars span.on { color: #facc15; }
.fb-stars .fb-none { font-size: 13px; letter-spacing: 0; color: #94a3b8; }
</style>

@endsection
