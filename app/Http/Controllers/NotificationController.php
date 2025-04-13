<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        $this->notificationService->updateRead(auth()->user());

        $notifications = $this->notificationService->getAllNotifications(auth()->user());

        return view('donatur.notifikasi', compact('notifications'));
    }

    public function show(Notification $notification)
    {
        // Check if notification belongs to current user
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        // Mark notification as read
        $notification->markAsRead();

        return view('notifications.show', compact('notification'));
    }

    public function markAsRead(Notification $notification)
    {
        // Check if notification belongs to current user
        if ($notification->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        Notification::where('user_id', auth()->id())
                   ->whereNull('read_at')
                   ->update(['read_at' => now()]);

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }

    public function destroy(Notification $notification)
    {
        // Check if notification belongs to current user
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->delete();

        return redirect()->route('notifications.index')
                         ->with('success', 'Notification deleted successfully.');
    }


}
