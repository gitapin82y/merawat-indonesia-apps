<?php
// app/Services/NotificationService.php
namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Mail\NotificationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class NotificationService
{
    public function createNotification(
        User $user, 
        string $title, 
        string $message, 
        ?string $type = 'system', 
        ?array $data = [], 
        ?string $imagePath = null
    ): Notification {
        $notification = Notification::create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'data' => $data,
            'image_path' => $imagePath
        ]);

        return $notification;
    }

    public function sendEmail(Notification $notification): bool
    {
        try {
            Mail::to($notification->user->email)->send(new NotificationMail($notification));
            $notification->markAsSent();
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to send notification email: ' . $e->getMessage());
            return false;
        }
    }

    public function createWithImage(
        User $user, 
        string $title, 
        string $message, 
        $image, 
        ?string $type = 'system', 
        ?array $data = []
    ): Notification {
        $imagePath = null;
        
        if ($image) {
            $imagePath = $image->store('notifications', 'public');
        }
        
        $notification = $this->createNotification($user, $title, $message, $type, $data, $imagePath);
        
        return $notification;
    }

    public function getUnreadNotifications(User $user, int $limit = 10)
    {
        return Notification::where('user_id', $user->id)
                          ->whereNull('read_at')
                          ->orderBy('created_at', 'desc')
                          ->limit($limit)
                          ->get();
    }

    public function getAllNotifications(User $user)
    {
        return Notification::where('user_id', $user->id)
                          ->orderBy('created_at', 'desc')
                          ->get();
    }

    public function updateRead(User $user)
    {
        return Notification::where('user_id', $user->id)
        ->whereNull('read_at')
        ->update(['read_at' => now()]);
    }
}