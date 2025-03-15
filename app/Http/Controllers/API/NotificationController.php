<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get the authenticated user's notifications.
     * Paginated and sorted by created_at in descending order.
     */
    public function index()
    {
        // Add debugging to see if Auth::id() is returning expected values
        \Log::info('Fetching notifications for user: ' . Auth::id());
        
        $notifications = Notification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        // Log how many notifications were found
        \Log::info('Found ' . $notifications->count() . ' notifications');
        
        return response()->json($notifications);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(Notification $notification)
    {
        // Check if the notification belongs to the authenticated user
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->update([
            'read' => true
        ]);

        return response()->json($notification);
    }

    /**
     * Mark all of the authenticated user's notifications as read.
     */
    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->where('read', false)
            ->update(['read' => true]);
        
        return response()->json(['message' => 'All notifications marked as read'], 200);
    }

    /**
     * Delete a specific notification.
     */
    public function destroy(Notification $notification)
    {
        // Check if the notification belongs to the authenticated user
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->delete();

        return response()->json(null, 204);
    }

    /**
     * Delete all of the authenticated user's notifications.
     */
    public function clearAll()
    {
        Notification::where('user_id', Auth::id())->delete();
        
        return response()->json(['message' => 'All notifications cleared'], 200);
    }
}