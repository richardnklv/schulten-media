<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    /**
     * Create a new notification
     *
     * @param User $user User who will receive the notification
     * @param string $title Notification title
     * @param string $content Notification content
     * @param string $type Notification type (task, project, comment)
     * @param mixed $related Related model (optional)
     * @return Notification
     */
    public function create(User $user, string $title, string $content, string $type, $related = null): Notification
    {
        try {
            // Log notification creation for debugging
            \Log::info("Creating notification: User: {$user->id}, Title: {$title}, Type: {$type}");
            
            $notification = new Notification();
            $notification->user_id = $user->id;
            $notification->title = $title;
            $notification->content = $content;
            $notification->type = $type;
            $notification->read = false;
            
            if ($related) {
                $notification->related()->associate($related);
            }
            
            $notification->save();
            
            \Log::info("Notification created successfully, ID: {$notification->id}");
            return $notification;
        } catch (\Exception $e) {
            // Log the error with more detail
            \Log::error('Error creating notification: ' . $e->getMessage());
            \Log::error('Error trace: ' . $e->getTraceAsString());
            
            // Return an unsaved notification object
            $notification = new Notification();
            $notification->user_id = $user->id;
            $notification->title = $title;
            $notification->content = $content;
            $notification->type = $type;
            $notification->read = false;
            
            return $notification;
        }
    }
    
    /**
     * Create notifications for multiple users
     *
     * @param array $userIds Array of user IDs
     * @param string $title Notification title
     * @param string $content Notification content
     * @param string $type Notification type (task, project, comment)
     * @param mixed $related Related model (optional)
     * @return array
     */
    public function createForMultipleUsers(array $userIds, string $title, string $content, string $type, $related = null): array
    {
        $notifications = [];
        
        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if ($user) {
                $notifications[] = $this->create($user, $title, $content, $type, $related);
            }
        }
        
        return $notifications;
    }
}