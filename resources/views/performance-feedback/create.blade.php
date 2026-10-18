@extends('layouts.app')

@section('content')

<div class="crm-dashboard crm-feedback-page">

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

            @if($errors->any())
                <div class="fb-form-alert" role="alert">
                    <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
                    <div><strong>Review the form details</strong><span>Please correct the highlighted fields and try again.</span></div>
                </div>
            @endif

            <div class="fb-field">
                <label for="receiver_id">Select user <span aria-hidden="true">*</span></label>
                <select id="receiver_id" name="receiver_id" class="fb-input" @if($errors->has('receiver_id')) aria-invalid="true" aria-describedby="receiver_id-error" @endif required>
                    <option value="">Choose user</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected(old('receiver_id') == $user->id)>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
                @error('receiver_id')<p id="receiver_id-error" class="fb-error"><i class="bi bi-exclamation-circle" aria-hidden="true"></i> {{ $message }}</p>@enderror
            </div>

            <div class="fb-field">
                <label for="message">Feedback <span aria-hidden="true">*</span></label>
                <textarea id="message" name="message" class="fb-input" rows="5" maxlength="2000"
                          @if($errors->has('message')) aria-invalid="true" aria-describedby="message-error" @endif
                          placeholder="Write performance feedback..." required>{{ old('message') }}</textarea>
                @error('message')<p id="message-error" class="fb-error"><i class="bi bi-exclamation-circle" aria-hidden="true"></i> {{ $message }}</p>@enderror
            </div>

            <fieldset class="fb-field fb-rating-field" @if($errors->has('rating')) aria-describedby="rating-error" @else aria-describedby="rating-help" @endif>
                <legend>Rating <span class="fb-rating-optional">Optional</span></legend>
                <div class="fb-rating-options" role="radiogroup" aria-label="Rating from one to five stars">
                    @for($rating = 1; $rating <= 5; $rating++)
                        <input
                            class="fb-rating-input"
                            type="radio"
                            id="rating-{{ $rating }}"
                            name="rating"
                            value="{{ $rating }}"
                            @checked((string) old('rating') === (string) $rating)
                            @if($errors->has('rating')) aria-invalid="true" aria-describedby="rating-error" @endif>
                        <label class="fb-rating-choice" for="rating-{{ $rating }}" title="{{ $rating }} out of 5 stars">
                            <span aria-hidden="true">&#9733;</span>
                            <span class="fb-rating-number">{{ $rating }}</span>
                            <span class="sr-only">{{ $rating }} {{ $rating === 1 ? 'star' : 'stars' }}</span>
                        </label>
                    @endfor
                </div>
                <p id="rating-help" class="fb-rating-help">Choose a rating from 1 to 5 stars, or leave it blank.</p>
                @error('rating')<p id="rating-error" class="fb-error"><i class="bi bi-exclamation-circle" aria-hidden="true"></i> {{ $message }}</p>@enderror
            </fieldset>

            <div class="fb-actions">
                <button type="submit" class="fb-btn fb-btn-lg"><i class="bi bi-send" aria-hidden="true"></i> Save feedback</button>
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

.fb-rating-field {
    min-width: 0;
    border: 0;
    padding: 0;
}
.fb-rating-field legend {
    margin-bottom: 8px;
    color: #e2e8f0;
    font-size: 14px;
    font-weight: 600;
}
.fb-rating-optional {
    margin-left: 6px;
    color: #94a3b8;
    font-size: 12px;
    font-weight: 400;
}
.fb-rating-options {
    display: flex;
    flex-wrap: wrap;
    gap: 9px;
}
.fb-rating-input {
    position: absolute;
    width: 1px;
    height: 1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    clip-path: inset(50%);
}
.fb-rating-choice {
    display: inline-flex;
    min-width: 58px;
    min-height: 48px;
    align-items: center;
    justify-content: center;
    gap: 6px;
    margin: 0;
    border: 1px solid rgba(255,255,255,.14);
    border-radius: 13px;
    padding: 8px 12px;
    background: rgba(255,255,255,.05);
    color: #94a3b8;
    font-size: 19px;
    cursor: pointer;
    transition: background-color .16s ease, border-color .16s ease, color .16s ease;
}
.fb-rating-choice:hover {
    border-color: rgba(250,204,21,.5);
    color: #fde68a;
}
.fb-rating-number {
    color: #cbd5e1;
    font-size: 12px;
    font-weight: 700;
}
.fb-rating-input:checked + .fb-rating-choice {
    border-color: rgba(250,204,21,.58);
    background: linear-gradient(145deg, rgba(250,204,21,.18), rgba(249,115,22,.12));
    color: #facc15;
    box-shadow: 0 0 0 3px rgba(250,204,21,.08);
}
.fb-rating-input:checked + .fb-rating-choice .fb-rating-number {
    color: #fef3c7;
}
.fb-rating-input:focus-visible + .fb-rating-choice {
    outline: 2px solid #93c5fd;
    outline-offset: 3px;
}
.fb-rating-help {
    margin: 8px 0 0;
    color: #94a3b8;
    font-size: 12px;
}
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

.fb-actions { display: flex; align-items: center; gap: 18px; }
.fb-btn {
    padding: 8px 14px; border: 0; border-radius: 10px; cursor: pointer; color: #fff; font-size: 12px;
    background: linear-gradient(135deg, #2563eb, #7c3aed);
}
.fb-btn-lg { padding: 12px 26px; font-size: 14px; font-weight: 600; border-radius: 12px; }
.fb-cancel { color: #94a3b8; text-decoration: none; font-size: 14px; }
.fb-cancel:hover { color: #fff; }

@media (max-width: 480px) {
    .fb-rating-options { gap: 7px; }
    .fb-rating-choice { min-width: 48px; min-height: 44px; padding: 7px 9px; }
}
</style>

@endsection
