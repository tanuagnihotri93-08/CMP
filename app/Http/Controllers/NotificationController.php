<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Mark a specific notification as read.
     */
    public function markRead(Request $request, string $notification): RedirectResponse|JsonResponse
    {
        $notif = $request->user()
            ->notifications()
            ->whereKey($notification)
            ->firstOrFail();

        $notif->markAsRead();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read.',
                'unread_count' => $request->user()->unreadNotifications()->count(),
            ]);
        }

        $actionUrl = $notif->data['action_url'] ?? null;
        $destination = $this->safeActionUrl($actionUrl);
        $fallback = $this->safeActionUrl(url()->previous()) ?? route('dashboard');

        return redirect()->to($destination ?? $fallback);
    }

    /**
     * Mark all unread notifications as read.
     */
    public function markAllRead(Request $request): RedirectResponse|JsonResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read.',
                'unread_count' => 0,
            ]);
        }

        return back();
    }

    /**
     * Delete a single notification.
     */
    public function destroy(Request $request, string $notification): RedirectResponse|JsonResponse
    {
        $request->user()
            ->notifications()
            ->whereKey($notification)
            ->firstOrFail()
            ->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Notification deleted successfully.',
                'unread_count' => $request->user()->unreadNotifications()->count(),
            ]);
        }

        return back();
    }

    /**
     * Clear all read notifications.
     */
    public function clearRead(Request $request): RedirectResponse|JsonResponse
    {
        $request->user()->readNotifications()->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Read notifications cleared.',
            ]);
        }

        return back();
    }

    private function safeActionUrl(mixed $actionUrl): ?string
    {
        if (! is_string($actionUrl) || $actionUrl === '' || preg_match('/[\x00-\x1F\x7F\\\\]/', $actionUrl)) {
            return null;
        }

        if (str_starts_with($actionUrl, '/') && ! str_starts_with($actionUrl, '//')) {
            return $actionUrl;
        }

        $destination = parse_url($actionUrl);
        $application = parse_url((string) config('app.url'));

        if (! is_array($destination) || ! is_array($application)
            || isset($destination['user']) || isset($destination['pass'])
            || ! isset($destination['scheme'], $destination['host'], $application['scheme'], $application['host'])) {
            return null;
        }

        $port = static fn (array $url): ?int => $url['port'] ?? match (strtolower($url['scheme'])) {
            'http' => 80,
            'https' => 443,
            default => null,
        };

        if (strtolower($destination['scheme']) !== strtolower($application['scheme'])
            || strtolower($destination['host']) !== strtolower($application['host'])
            || $port($destination) !== $port($application)) {
            return null;
        }

        return $actionUrl;
    }
}
