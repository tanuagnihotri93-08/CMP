@extends('layouts.app')

@section('content')

<div class="crm-dashboard">

    <div class="hero-card feedback-hero">
        <div>
            <p class="dashboard-eyebrow">PERFORMANCE MANAGEMENT</p>
            <h1>Add Performance Feedback</h1>
            <p class="sub-text">Provide feedback to team members based on their performance.</p>
        </div>
    </div>

    <div class="fb-card fb-form-card">

        <form method="POST" action="{{ route('performance-feedback.store') }}" novalidate>
            @csrf

            <div class="fb-field">
                <label for="receiver_id">Select User</label>
                <select id="receiver_id" name="receiver_id" class="fb-input" required>
                    <option value="">Choose user</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected(old('receiver_id') == $user->id)>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
                @error('receiver_id')<p class="fb-error">{{ $message }}</p>@enderror
            </div>

            <div class="fb-field">
                <label for="message">Feedback</label>
                <textarea id="message" name="message" class="fb-input" rows="5" maxlength="2000"
                          placeholder="Write performance feedback..." required>{{ old('message') }}</textarea>
                @error('message')<p class="fb-error">{{ $message }}</p>@enderror
            </div>

            <div class="fb-actions">
                <button type="submit" class="fb-btn fb-btn-lg">Save Feedback</button>
                <a href="{{ route('performance-feedback.index') }}" class="fb-cancel">Cancel</a>
            </div>

        </form>

    </div>

</div>

<style>
.feedback-hero {
    display: flex; justify-content: space-between; align-items: center; gap: 20px;
    padding: 36px 42px; margin-bottom: 28px; border-radius: 28px;
    background: linear-gradient(135deg, rgba(37,99,235,.18), rgba(124,58,237,.18));
    border: 1px solid rgba(255,255,255,.12);
}
.feedback-hero h1 { margin: 8px 0 10px; color: #fff; }

.fb-card {
    padding: 28px; border-radius: 24px;
    background: linear-gradient(145deg, rgba(255,255,255,.10), rgba(255,255,255,.03));
    border: 1px solid rgba(255,255,255,.12);
}
.fb-form-card { max-width: 720px; }

.fb-field { margin-bottom: 22px; }
.fb-field label { display: block; margin-bottom: 8px; color: #cbd5e1; font-size: 14px; font-weight: 600; }

.fb-input {
    width: 100%; padding: 13px 16px; border-radius: 14px; font-size: 14px; font-family: inherit;
    color: #f1f5f9; background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.14);
    outline: none; transition: border-color .25s ease, box-shadow .25s ease;
}
.fb-input:focus { border-color: #60a5fa; box-shadow: 0 0 0 3px rgba(96,165,250,.25); }
.fb-input::placeholder { color: #64748b; }
select.fb-input option { background: #0f172a; color: #f1f5f9; }
textarea.fb-input { resize: vertical; min-height: 120px; }

.fb-error { margin: 8px 0 0; color: #fca5a5; font-size: 13px; }

.fb-actions { display: flex; align-items: center; gap: 18px; }
.fb-btn {
    padding: 8px 14px; border: 0; border-radius: 10px; cursor: pointer; color: #fff; font-size: 12px;
    background: linear-gradient(135deg, #2563eb, #7c3aed);
}
.fb-btn-lg { padding: 12px 26px; font-size: 14px; font-weight: 600; border-radius: 12px; }
.fb-cancel { color: #94a3b8; text-decoration: none; font-size: 14px; }
.fb-cancel:hover { color: #fff; }
</style>

@endsection