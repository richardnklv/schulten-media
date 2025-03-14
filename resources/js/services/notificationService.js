import { reactive } from 'vue';

// Create a reactive state for notifications
const state = reactive({
  notifications: [],
  unreadCount: 0
});

// Max number of notifications to keep
const MAX_NOTIFICATIONS = 50;

export default {
  // Get all notifications
  getNotifications() {
    return state.notifications;
  },
  
  // Get unread count
  getUnreadCount() {
    return state.unreadCount;
  },
  
  // Add a new notification
  addNotification(notification) {
    // Ensure notification has all required fields
    const newNotification = {
      id: Date.now().toString(), // Generate a unique ID
      read: false,
      ...notification,
      time: notification.time || new Date()
    };
    
    // Add to the beginning of the array
    state.notifications.unshift(newNotification);
    
    // Trim the array if it exceeds the maximum size
    if (state.notifications.length > MAX_NOTIFICATIONS) {
      state.notifications = state.notifications.slice(0, MAX_NOTIFICATIONS);
    }
    
    // Increment unread count
    state.unreadCount++;
    
    // Return the new notification
    return newNotification;
  },
  
  // Mark a notification as read
  markAsRead(notificationId) {
    const notification = state.notifications.find(n => n.id === notificationId);
    if (notification && !notification.read) {
      notification.read = true;
      state.unreadCount = Math.max(0, state.unreadCount - 1);
    }
  },
  
  // Mark all notifications as read
  markAllAsRead() {
    state.notifications.forEach(notification => {
      notification.read = true;
    });
    state.unreadCount = 0;
  },
  
  // Clear all notifications
  clearAll() {
    state.notifications = [];
    state.unreadCount = 0;
  }
};