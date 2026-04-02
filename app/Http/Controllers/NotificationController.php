<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Mark a single database notification as read, then redirect (usually to dashboard).
     */
    public function markAsRead(Request $request, string $id): RedirectResponse
    {
        $notification = $request->user()->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();

        $data = $notification->data;
        $url = is_array($data) && ! empty($data['url']) ? $data['url'] : route('candidate.dashboard');

        return redirect()->to($url);
    }

    /**
     * Mark all recent (within retention window) unread notifications as read.
     */
    public function markAllRead(Request $request): RedirectResponse
    {
        $days = (int) config('hirevo.notification_retention_days', 14);
        $request->user()
            ->unreadNotifications()
            ->where('created_at', '>=', now()->subDays($days))
            ->get()
            ->each
            ->markAsRead();

        return redirect()->back();
    }
}
