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

        // Agar notification data mein action_url diya ho toh wahan redirect karein, warna pichle page par
        $actionUrl = $notif->data['action_url'] ?? null;
        
        return $actionUrl ? redirect($actionUrl) : back();
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
}