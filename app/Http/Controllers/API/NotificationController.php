<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponseTrait;

class NotificationController extends Controller
{
    use ApiResponseTrait;

    /**
     * Get all notifications for authenticated user
     */
    public function index()
    {
        $user = Auth::user();
        $notifications = $user->notifications()->orderBy('created_at', 'desc')->paginate(10);

        return $this->successResponse($notifications, 'Notifications fetched successfully');
    }

    /**
     * Count unread notifications
     */
    public function unreadCount()
    {
        $user = Auth::user();
        $count = $user->notifications()->where('is_read', false)->count();

        return $this->successResponse(['unread_count' => $count], 'Unread notifications count fetched');
    }

    /**
     * Mark a single notification as read
     */
    public function markAsRead($id)
    {
        $user = Auth::user();
        $notification = Notification::where('id', $id)->where('user_id', $user->id)->first();

        if (!$notification) {
            return $this->errorResponse('Notification not found', 404);
        }

        $notification->is_read = true;
        $notification->save();

        return $this->successResponse($notification, 'Notification marked as read');
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        $user->notifications()->update(['is_read' => true]);

        return $this->successResponse([], 'All notifications marked as read');
    }

    /**
     * Delete a single notification
     */
    public function delete($id)
    {
        $user = Auth::user();
        $notification = Notification::where('id', $id)->where('user_id', $user->id)->first();

        if (!$notification) {
            return $this->errorResponse('Notification not found', 404);
        }

        $notification->delete();

        return $this->successResponse([], 'Notification deleted successfully');
    }

    /**
     * Delete all notifications
     */
    public function deleteAll()
    {
        $user = Auth::user();
        $user->notifications()->delete();

        return $this->successResponse([], 'All notifications deleted successfully');
    }

    /**
     * Send a notification (for internal use)
     */
    public function sendNotification($userId, $actorId = null, $type, $data = [])
    {
        $notification = Notification::create([
            'user_id' => $userId,
            'actor_id' => $actorId,
            'type' => $type,
            'data' => $data,
            'is_read' => false,
        ]);

        return $notification;
    }
}
