<template>
  <div class="notifications-panel">
    <div class="notifications-header">
      <div class="notifications-actions" v-if="notifications.length > 0">
        <button @click="clearAllNotifications" class="clear-btn">Clear All</button>
        <button @click="markAllAsRead" class="read-btn">Mark All Read</button>
      </div>
    </div>
    
    <div class="notifications-empty" v-if="notifications.length === 0">
      <span class="empty-icon">
        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="1.5" fill="none">
          <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
          <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
        </svg>
      </span>
      <p>No new notifications</p>
    </div>
    
    <div class="notifications-list" v-else>
      <div 
        v-for="notification in notifications" 
        :key="notification.id" 
        class="notification-item"
        :class="{ 'unread': !notification.read }"
        @click="markAsRead(notification.id)"
      >
        <div class="notification-icon" :class="getNotificationTypeClass(notification.type)">
          <svg v-if="notification.type.includes('task')" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none">
            <path d="M9 11l3 3L22 4"></path>
            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
          </svg>
          <svg v-else-if="notification.type.includes('project')" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none">
            <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
            <polyline points="2 17 12 22 22 17"></polyline>
            <polyline points="2 12 12 17 22 12"></polyline>
          </svg>
          <svg v-else-if="notification.type.includes('comment')" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
          </svg>
        </div>
        
        <div class="notification-content">
          <div class="notification-title">{{ notification.title }}</div>
          <div class="notification-message">{{ notification.content }}</div>
          <div class="notification-time">{{ formatTime(notification.time) }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import notificationService from '../../services/notificationService';

export default {
  name: 'NotificationsPanel',
  data() {
    return {
      notifications: []
    };
  },
  mounted() {
    console.log('NotificationsPanel mounted, fetching notifications...');
    
    // Get initial notifications from API
    notificationService.fetchNotifications().then(() => {
      console.log('Initial fetch complete');
      this.updateNotifications();
    });
    
    // Set up interval to refresh notifications (every 10 seconds)
    this.interval = setInterval(() => {
      console.log('Refreshing notifications...');
      notificationService.fetchNotifications().then(() => {
        this.updateNotifications();
      });
    }, 10000);
  },
  beforeUnmount() {
    // Clear interval when component is destroyed
    clearInterval(this.interval);
  },
  methods: {
    updateNotifications() {
      this.notifications = notificationService.getNotifications();
    },
    async markAsRead(id) {
      await notificationService.markAsRead(id);
      this.updateNotifications();
    },
    async markAllAsRead() {
      await notificationService.markAllAsRead();
      this.updateNotifications();
    },
    async clearAllNotifications() {
      await notificationService.clearAll();
      this.updateNotifications();
    },
    formatTime(timestamp) {
      if (!timestamp) return '';
      
      // Handle both date strings from the API and Date objects from local notifications
      const date = timestamp instanceof Date ? timestamp : new Date(timestamp);
      const now = new Date();
      const diffMs = now - date;
      const diffMins = Math.round(diffMs / 60000);
      
      if (diffMins < 1) {
        return 'Just now';
      } else if (diffMins < 60) {
        return `${diffMins} min ago`;
      } else if (diffMins < 1440) {
        const hours = Math.floor(diffMins / 60);
        return `${hours} hour${hours !== 1 ? 's' : ''} ago`;
      } else {
        const days = Math.floor(diffMins / 1440);
        return `${days} day${days !== 1 ? 's' : ''} ago`;
      }
    },
    getNotificationTypeClass(type) {
      if (type.includes('task')) {
        return 'task-notification';
      } else if (type.includes('project')) {
        return 'project-notification';
      } else if (type.includes('comment')) {
        return 'comment-notification';
      }
      return '';
    }
  }
};
</script>

<style scoped>
.notifications-panel {
  display: flex;
  flex-direction: column;
  height: 100%;
}

.notifications-header {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  margin-bottom: 0.8rem;
}


.notifications-actions {
  display: flex;
  gap: 0.5rem;
}

.clear-btn, .read-btn {
  background: none;
  border: none;
  color: #777;
  font-size: 0.7rem;
  cursor: pointer;
  padding: 0.25rem 0.4rem;
  border-radius: 3px;
  transition: all 0.2s ease;
}

.clear-btn:hover, .read-btn:hover {
  background-color: #333;
  color: #fff;
}

.notifications-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 1rem;
  color: #777;
  text-align: center;
}

.empty-icon {
  margin-bottom: 0.5rem;
  opacity: 0.5;
}

.notifications-empty p {
  font-size: 0.75rem;
  margin: 0;
}

.notifications-list {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
  overflow-y: auto;
}

.notification-item {
  display: flex;
  padding: 0.8rem;
  background-color: #2a2a2a;
  border-radius: 6px;
  gap: 0.8rem;
  cursor: pointer;
  transition: all 0.2s ease;
  position: relative;
}

.notification-item:hover {
  background-color: #333;
}

.notification-item.unread {
  background-color: rgba(138, 86, 255, 0.1);
}

.notification-item.unread::before {
  content: '';
  position: absolute;
  top: 0.8rem;
  right: 0.8rem;
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background-color: #8a56ff;
}

.notification-icon {
  width: 28px;
  height: 28px;
  min-width: 28px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.task-notification {
  background-color: #8a56ff;
}

.project-notification {
  background-color: #3498db;
}

.comment-notification {
  background-color: #2ecc71;
}

.notification-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
}

.notification-title {
  font-size: 0.8rem;
  font-weight: 500;
}

.notification-message {
  font-size: 0.75rem;
  color: #aaa;
  line-height: 1.3;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.notification-time {
  font-size: 0.65rem;
  color: #777;
  margin-top: 0.2rem;
}
</style>