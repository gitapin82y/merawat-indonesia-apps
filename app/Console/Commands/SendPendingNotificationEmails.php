<?php
// app/Console/Commands/SendPendingNotificationEmails.php
namespace App\Console\Commands;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendPendingNotificationEmails extends Command
{
    protected $signature = 'notifications:send-pending';
    protected $description = 'Send emails for notifications that have not been sent yet';

    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    public function handle()
    {
        $pendingNotifications = Notification::where('is_sent_email', false)->get();
        
        $this->info("Found {$pendingNotifications->count()} pending notification emails.");
        
        $successCount = 0;
        
        foreach ($pendingNotifications as $notification) {
            $this->info("Sending notification email #{$notification->id} to user #{$notification->user_id}");
            
            $result = $this->notificationService->sendEmail($notification);
            
            if ($result) {
                $successCount++;
                $this->info("Email sent successfully.");
            } else {
                $this->error("Failed to send email for notification #{$notification->id}");
            }
        }
        
        $this->info("Processed {$pendingNotifications->count()} notifications. Successfully sent {$successCount} emails.");
        
        return 0;
    }
}