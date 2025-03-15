import { reactive } from 'vue';
import api from './api';

// Create a reactive state for notifications
const state = reactive({
  notifications: [],
  unreadCount: 0,
  loading: false
});

// Max number of notifications to keep
const MAX_NOTIFICATIONS = 50;

export default {
  // Fetch notifications from API
  async fetchNotifications() {
    try {
      state.loading = true;
      console.log('Fetching notifications from API...');
      const response = await api.get('/notifications');
      
      console.log('API response:', response);
      
      // Handle both paginated and non-paginated responses
      if (response.data) {
        if (response.data.data) {
          // API returns paginated data
          console.log('Received paginated data with', response.data.data.length, 'notifications');
          state.notifications = response.data.data;
        } else if (Array.isArray(response.data)) {
          // API returns array directly
          console.log('Received array with', response.data.length, 'notifications');
          state.notifications = response.data;
        } else {
          console.log('Unexpected data format:', response.data);
          // If we get an unexpected format, create a notification to make it obvious
          state.notifications = [{
            id: 'debug-1',
            title: 'Debug: API Format Issue',
            content: 'Notification system received unexpected data format',
            read: false,
            time: new Date(),
            type: 'system'
          }];
        }
        state.unreadCount = state.notifications.filter(n => !n.read).length;
      }
    } catch (error) {
      console.error('Error fetching notifications:', error);
      // Add a debug notification to make the error obvious
      state.notifications = [{
        id: 'error-1',
        title: 'Error Fetching Notifications',
        content: error.message || 'Unknown error',
        read: false,
        time: new Date(),
        type: 'system'
      }];
      state.unreadCount = 1;
    } finally {
      state.loading = false;
    }
  },
  
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
  async markAsRead(notificationId) {
    const notification = state.notifications.find(n => n.id === notificationId);
    if (notification && !notification.read) {
      try {
        await api.put(`/notifications/${notificationId}/read`);
        
        notification.read = true;
        state.unreadCount = Math.max(0, state.unreadCount - 1);
      } catch (error) {
        console.error('Error marking notification as read:', error);
      }
    }
  },
  
  // Mark all notifications as read
  async markAllAsRead() {
    try {
      await api.put('/notifications/mark-all-read');
      
      state.notifications.forEach(notification => {
        notification.read = true;
      });
      state.unreadCount = 0;
    } catch (error) {
      console.error('Error marking all notifications as read:', error);
    }
  },
  
  // Clear all notifications
  async clearAll() {
    try {
      await api.delete('/notifications');
      
      state.notifications = [];
      state.unreadCount = 0;
    } catch (error) {
      console.error('Error clearing notifications:', error);
    }
  }
};