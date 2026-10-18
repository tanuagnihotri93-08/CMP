<?php

namespace App\Http\Controllers;

use App\Models\PerformanceFeedback;
use App\Models\User;
use App\Notifications\PerformanceFeedbackNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PerformanceFeedbackController extends Controller
{
    /**
     * Roles jinko current user feedback de sakta hai.
     */
    private function receivableRoles(): array
    {
        return match (auth()->user()->role?->name) {
            'super_admin' => ['manager', 'employee'],
            'manager' => ['employee'],
            default => [],
        };
    }

    public function index()
    {
        $user = auth()->user();
        $roleName = $user->role?->name;

        $query = PerformanceFeedback::with(['sender', 'receiver'])->latest();

        if ($roleName === 'super_admin') {
            // sab dekh sakta hai
        } elseif ($roleName === 'manager') {
            $query->where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)
                  ->orWhere('receiver_id', $user->id);
            });
        } else {
            $query->where('receiver_id', $user->id);
        }

        $feedbacks = $query->paginate(25)->withQueryString();

        return view('performance-feedback.index', compact('feedbacks'));
    }

    public function create()
    {
        $allowedRoles = $this->receivableRoles();

        abort_if(empty($allowedRoles), 403);

        $users = User::where('is_active', true)
            ->whereHas('role', fn ($q) => $q->whereIn('name', $allowedRoles))
            ->select(['id', 'name'])
            ->orderBy('id')
            ->lazy(500);

        return view('performance-feedback.create', compact('users'));
    }

    public function store(Request $request)
    {
        $allowedRoles = $this->receivableRoles();

        abort_if(empty($allowedRoles), 403);

        $validated = $request->validate([
            'receiver_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where('is_active', true),
            ],
            'message' => ['required', 'string', 'min:2', 'max:2000'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
        ]);

        $receiverAllowed = User::where('id', $validated['receiver_id'])
            ->where('is_active', true)
            ->whereHas('role', fn ($q) => $q->whereIn('name', $allowedRoles))
            ->exists();

        abort_unless($receiverAllowed, 403);

        DB::transaction(function () use ($validated): void {
            $feedback = PerformanceFeedback::create([
                'sender_id' => auth()->id(),
                'receiver_id' => $validated['receiver_id'],
                'message' => $validated['message'],
                'rating' => $validated['rating'] ?? null,
                'status' => 'Open',
            ]);

            $feedback->receiver->notify(new PerformanceFeedbackNotification($feedback));
        }, attempts: 3);

        return redirect()
            ->route('performance-feedback.index')
            ->with('success', 'Feedback added successfully.');
    }

    public function updateStatus(Request $request, PerformanceFeedback $feedback)
    {
        $user = auth()->user();

        $canUpdate = $user->role?->name === 'super_admin'
            || $feedback->receiver_id === $user->id
            || $feedback->sender_id === $user->id;

        abort_unless($canUpdate, 403);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['Open', 'Reviewed'])],
        ]);

        $feedback->update(['status' => $validated['status']]);

        return back()->with('success', 'Feedback status updated.');
    }
}
