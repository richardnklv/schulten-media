<template>
  <div class="task-details">
    <div class="detail-section">
      <div class="detail-grid">
        <div class="detail-item">
          <span class="detail-label">Priority</span>
          <span class="detail-value" :class="'priority-' + task.priority.toLowerCase().replace(' ', '-')">
            {{ task.priority }}
          </span>
        </div>
        
        <div class="detail-item">
          <span class="detail-label">Status</span>
          <div class="status-dropdown">
            <button 
              @click="toggleStatusDropdown" 
              class="status-current" 
              :class="'status-' + task.status.toLowerCase().replace(' ', '-')"
            >
              {{ task.status }}
              <svg viewBox="0 0 24 24" width="12" height="12" stroke="currentColor" stroke-width="2" fill="none">
                <polyline points="6 9 12 15 18 9"></polyline>
              </svg>
            </button>
            <div class="status-options" v-if="showStatusDropdown">
              <button 
                v-for="status in availableStatuses" 
                :key="status"
                :class="['status-option', 'status-' + status.toLowerCase().replace(' ', '-')]"
                @click="changeStatus(status)"
              >
                {{ status }}
              </button>
            </div>
          </div>
        </div>
        
        <div class="detail-item">
          <span class="detail-label">Due Date</span>
          <span class="detail-value">{{ formatDate(task.due_date) }}</span>
        </div>
        
        <div class="detail-item">
          <span class="detail-label">Assignee</span>
          <div class="assignee-selector">
            <div v-if="task.assignee" class="assignees-list">
              <div class="assignee-item">
                <span class="assignee-avatar">{{ getInitials(task.assignee.name) }}</span>
                <span class="assignee-name">{{ task.assignee.name }}</span>
              </div>
            </div>
            <div v-else class="no-assignee">
              <span>Unassigned</span>
            </div>
            
            <button @click="toggleAssigneeDropdown" class="add-assignee-btn" title="Add assignee">
              <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2" fill="none">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="16"></line>
                <line x1="8" y1="12" x2="16" y2="12"></line>
              </svg>
            </button>
            
            <!-- Assignee dropdown -->
            <div v-if="showAssigneeDropdown" class="assignee-dropdown">
              <div class="search-container">
                <input 
                  type="text" 
                  v-model="searchQuery" 
                  placeholder="Search users..." 
                  class="assignee-search"
                  ref="searchInput"
                  @input="filterUsers"
                >
              </div>
              
              <div class="assignee-list">
                <div 
                  v-for="user in filteredUsers" 
                  :key="user.id" 
                  class="assignee-option"
                  @click="assignUser(user)"
                >
                  <div class="assignee-avatar">{{ getInitials(user.name) }}</div>
                  <span class="assignee-name">{{ user.name }}</span>
                </div>
                
                <div v-if="filteredUsers.length === 0 && !isLoadingUsers" class="no-results">
                  No users found
                </div>
                
                <div v-if="isLoadingUsers" class="loading-users">
                  <div class="spinner small"></div>
                  <span>Loading users...</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="description-box">
        <h4>Description</h4>
        <p>{{ task.description || 'No description provided' }}</p>
      </div>
      
      <!-- Attachments Section -->
      <div class="attachments-section">
        <div class="attachments-header">
          <h4>Attachments</h4>
          <button 
            class="add-attachment-btn" 
            @click="$refs.fileInput.click()" 
            title="Add attachment"
          >
            <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
              <polyline points="17 8 12 3 7 8"></polyline>
              <line x1="12" y1="3" x2="12" y2="15"></line>
            </svg>
            <span>Add Files</span>
          </button>
          <input 
            type="file" 
            ref="fileInput" 
            @change="handleFileUpload" 
            multiple 
            style="display: none"
          >
        </div>
        
        <div v-if="isUploading" class="uploading-indicator">
          <div class="spinner"></div>
          <span>Uploading {{ filesToUpload.length }} file(s)...</span>
        </div>
        
        <div v-if="!task.attachments || task.attachments.length === 0" class="empty-attachments">
          No attachments yet
        </div>
        
        <div v-else class="attachments-list">
          <div 
            v-for="attachment in task.attachments" 
            :key="attachment.id" 
            class="attachment-item"
          >
            <!-- File icon based on type -->
            <div class="attachment-icon">
              <svg v-if="isImageFile(attachment.file_type)" viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="1.5" fill="none">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                <polyline points="21 15 16 10 5 21"></polyline>
              </svg>
              <svg v-else-if="isPdfFile(attachment.file_type)" viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="1.5" fill="none">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
              </svg>
              <svg v-else viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="1.5" fill="none">
                <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path>
                <polyline points="13 2 13 9 20 9"></polyline>
              </svg>
            </div>
            
            <!-- File name and size -->
            <div class="attachment-details">
              <span class="attachment-name" :title="attachment.original_filename">
                {{ truncateFilename(attachment.original_filename) }}
              </span>
              <span class="attachment-size">{{ formatFileSize(attachment.file_size) }}</span>
            </div>
            
            <!-- Download button -->
            <a 
              :href="`/storage/${attachment.file_path.replace('public/', '')}`" 
              target="_blank" 
              download 
              class="download-btn" 
              title="Download"
            >
              <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="7 10 12 15 17 10"></polyline>
                <line x1="12" y1="15" x2="12" y2="3"></line>
              </svg>
            </a>
          </div>
        </div>
      </div>
    </div>
    
    <div class="timer-display" v-if="isTimerRunning || timeSpent > 0">
      <div class="time-spent">
        <span class="time-label">Time Focused</span>
        <span class="time-value">{{ formatTime(timeSpent) }}</span>
      </div>
      <div class="timer-progress">
        <div class="progress-circle">
          <svg viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="45" fill="transparent" stroke="#2a2a2a" stroke-width="5" />
            <circle 
              cx="50" 
              cy="50" 
              r="45" 
              fill="transparent" 
              stroke="#8a56ff" 
              stroke-width="5" 
              stroke-dasharray="282.7"
              :stroke-dashoffset="progressOffset"
              transform="rotate(-90 50 50)"
            />
          </svg>
          <div class="progress-text">
            <span class="percentage">{{ progressPercentage }}%</span>
            <span class="label">of goal</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import taskService from '../../services/taskService';
import notificationService from '../../services/notificationService';
import userService from '../../services/userService';

export default {
  name: 'TaskDetails',
  props: {
    task: {
      type: Object,
      required: true
    },
    timeSpent: {
      type: Number,
      default: 0
    },
    isTimerRunning: {
      type: Boolean,
      default: false
    },
    goalTime: {
      type: Number,
      default: 6 * 60 * 60 // 6 hours in seconds
    }
  },
  data() {
    return {
      showStatusDropdown: false,
      showAssigneeDropdown: false,
      isUpdating: false,
      isUploading: false,
      isLoadingUsers: false,
      filesToUpload: [],
      searchQuery: '',
      availableStatuses: ['To Do', 'In Progress', 'Under Review', 'Completed'],
      users: [],
      filteredUsers: [],
      assignees: []
    }
  },
  emits: ['status-updated', 'task-updated'],
  computed: {
    progressPercentage() {
      return Math.min(Math.round((this.timeSpent / this.goalTime) * 100), 100);
    },
    progressOffset() {
      // Circle circumference formula: 2�r = 2 * Math.PI * 45 H 282.7
      const circumference = 2 * Math.PI * 45;
      return circumference - (circumference * this.progressPercentage / 100);
    }
  },
  mounted() {
    document.addEventListener('click', this.handleClickOutside);
    this.loadUsers();
  },
  beforeUnmount() {
    document.removeEventListener('click', this.handleClickOutside);
  },
  methods: {
    formatDate(dateString) {
      const options = { year: 'numeric', month: 'short', day: 'numeric' };
      return new Date(dateString).toLocaleDateString(undefined, options);
    },
    formatTime(seconds) {
      const hours = Math.floor(seconds / 3600);
      const minutes = Math.floor((seconds % 3600) / 60);
      const secs = seconds % 60;
      
      return `${hours}h ${minutes}m ${secs}s`;
    },
    formatFileSize(bytes) {
      if (bytes === 0) return '0 Bytes';
      
      const k = 1024;
      const sizes = ['Bytes', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    },
    truncateFilename(filename, maxLength = 20) {
      if (filename.length <= maxLength) return filename;
      
      const extension = filename.split('.').pop();
      const nameWithoutExtension = filename.substring(0, filename.lastIndexOf('.'));
      
      // Truncate the name part, keeping the extension
      const truncatedName = nameWithoutExtension.substring(0, maxLength - extension.length - 3) + '...';
      
      return truncatedName + '.' + extension;
    },
    isImageFile(mimeType) {
      return mimeType && mimeType.startsWith('image/');
    },
    isPdfFile(mimeType) {
      return mimeType === 'application/pdf';
    },
    toggleStatusDropdown() {
      this.showStatusDropdown = !this.showStatusDropdown;
    },
    toggleAssigneeDropdown() {
      this.showAssigneeDropdown = !this.showAssigneeDropdown;
      if (this.showAssigneeDropdown) {
        this.$nextTick(() => {
          if (this.$refs.searchInput) {
            this.$refs.searchInput.focus();
          }
          if (this.users.length === 0) {
            this.loadUsers();
          }
        });
      }
    },
    getInitials(name) {
      if (!name) return '';
      return name.split(' ')
        .map(part => part.charAt(0))
        .join('')
        .toUpperCase()
        .substring(0, 2);
    },
    async loadUsers() {
      try {
        this.isLoadingUsers = true;
        const response = await userService.getAll();
        this.users = response.data;
        this.filteredUsers = [...this.users];
      } catch (error) {
        console.error('Error loading users:', error);
      } finally {
        this.isLoadingUsers = false;
      }
    },
    filterUsers() {
      if (!this.searchQuery.trim()) {
        this.filteredUsers = [...this.users];
        return;
      }
      
      const query = this.searchQuery.toLowerCase();
      this.filteredUsers = this.users.filter(user => 
        user.name.toLowerCase().includes(query) || 
        (user.email && user.email.toLowerCase().includes(query))
      );
    },
    async assignUser(user) {
      if (this.isUpdating) return;
      
      // If the user is already the assignee, do nothing
      if (this.task.assignee && this.task.assignee.id === user.id) {
        this.showAssigneeDropdown = false;
        return;
      }
      
      try {
        this.isUpdating = true;
        
        // Create a copy of the task to update
        const updatedTask = { ...this.task, assignee_id: user.id };
        
        // Call the API to update the task
        const response = await taskService.update(this.task.id, updatedTask);
        
        // Emit an event to notify parent component
        this.$emit('task-updated', response.data);
        
        // Close the dropdown
        this.showAssigneeDropdown = false;
      } catch (error) {
        console.error('Error updating task assignee:', error);
        alert('Failed to update assignee. Please try again.');
      } finally {
        this.isUpdating = false;
      }
    },
    async handleFileUpload(event) {
      const files = event.target.files;
      if (!files || files.length === 0) return;
      
      this.filesToUpload = Array.from(files);
      this.isUploading = true;
      
      try {
        // Upload files using taskService - now using the dedicated endpoint
        const response = await taskService.addAttachments(this.task.id, this.filesToUpload);
        
        // The response now contains the updated task with attachments
        const updatedTask = response.data;
        
        // Emit event to update task in parent component
        this.$emit('task-updated', updatedTask);
        
        // Clear the file input
        this.$refs.fileInput.value = '';
      } catch (error) {
        console.error('Error uploading attachments:', error);
        alert('Failed to upload attachments. Please try again.');
      } finally {
        this.isUploading = false;
        this.filesToUpload = [];
      }
    },
    async changeStatus(newStatus) {
      if (this.isUpdating || newStatus === this.task.status) {
        this.showStatusDropdown = false;
        return;
      }
      
      try {
        this.isUpdating = true;
        
        // Create a copy of the task to update
        const updatedTask = { ...this.task, status: newStatus };
        
        // Call the API to update the task
        await taskService.update(this.task.id, updatedTask);
        
        // Emit an event to notify parent component
        this.$emit('status-updated', updatedTask);
        this.$emit('task-updated', updatedTask);
        
        // Close the dropdown
        this.showStatusDropdown = false;
      } catch (error) {
        console.error('Error updating task status:', error);
        alert('Failed to update task status. Please try again.');
      } finally {
        this.isUpdating = false;
      }
    },
    // Method to handle clicks outside the dropdown
    handleClickOutside(event) {
      // Handle clicks outside status dropdown
      const statusDropdown = this.$el.querySelector('.status-dropdown');
      if (statusDropdown && !statusDropdown.contains(event.target)) {
        this.showStatusDropdown = false;
      }
      
      // Handle clicks outside assignee dropdown
      const assigneeDropdown = this.$el.querySelector('.assignee-dropdown');
      if (assigneeDropdown && !assigneeDropdown.contains(event.target)) {
        const addAssigneeBtn = this.$el.querySelector('.add-assignee-btn');
        if (addAssigneeBtn && !addAssigneeBtn.contains(event.target)) {
          this.showAssigneeDropdown = false;
        }
      }
    }
  },
  // Close dropdown when clicking outside
  mounted() {
    document.addEventListener('click', this.handleClickOutside);
    this.loadUsers();
  },
  beforeUnmount() {
    document.removeEventListener('click', this.handleClickOutside);
  }
}
</script>

<style scoped>
.task-details {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.detail-section {
  background-color: #252525;
  padding: 1.25rem;
  border-radius: 6px;
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
  gap: 1.25rem;
  margin-bottom: 1.25rem;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
}

.detail-label {
  font-size: 0.7rem;
  color: #999;
  letter-spacing: 0.5px;
}

.detail-value {
  font-size: 0.85rem;
  font-weight: 500;
}

.detail-value.priority-must-be-done {
  color: #e74c3c;
}

.detail-value.priority-important {
  color: #f39c12;
}

.detail-value.priority-good-to-have {
  color: #2ecc71;
}

.detail-value.status-to-do {
  color: #999;
}

.detail-value.status-in-progress {
  color: #3498db;
}

.detail-value.status-under-review {
  color: #9b59b6;
}

.detail-value.status-completed {
  color: #2ecc71;
}

/* Status dropdown styles */
.status-dropdown {
  position: relative;
  width: 100%;
}

.status-current {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  padding: 0.4rem 0.6rem;
  border: none;
  background-color: #2a2a2a;
  border-radius: 4px;
  color: inherit;
  font-size: 0.85rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s ease;
  text-align: left;
}

.status-current svg {
  margin-left: 5px;
  transition: transform 0.15s ease;
}

.status-current:hover {
  background-color: #333;
}

.status-options {
  position: absolute;
  top: calc(100% + 4px);
  left: 0;
  width: 100%;
  background-color: #2a2a2a;
  border-radius: 4px;
  overflow: hidden;
  z-index: 10;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  animation: fadeIn 0.15s ease;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-5px); }
  to { opacity: 1; transform: translateY(0); }
}

.status-option {
  display: block;
  width: 100%;
  padding: 0.5rem 0.6rem;
  border: none;
  background-color: transparent;
  text-align: left;
  color: inherit;
  font-size: 0.85rem;
  cursor: pointer;
  transition: all 0.15s ease;
}

.status-option:hover {
  background-color: #333;
}

/* Status colors for dropdown */
.status-current.status-to-do,
.status-option.status-to-do {
  color: #999;
}

.status-current.status-in-progress,
.status-option.status-in-progress {
  color: #3498db;
}

.status-current.status-under-review,
.status-option.status-under-review {
  color: #9b59b6;
}

.status-current.status-completed,
.status-option.status-completed {
  color: #2ecc71;
}

.description-box {
  background-color: #2a2a2a;
  padding: 0.8rem;
  border-radius: 6px;
}

.description-box h4 {
  font-size: 0.8rem;
  margin-bottom: 0.4rem;
  margin-top: 0;
  color: #ccc;
  letter-spacing: 0.5px;
}

.description-box p {
  color: #aaa;
  font-size: 0.8rem;
  line-height: 1.4;
  margin: 0;
}

/* Attachments section styles */
.attachments-section {
  margin-top: 1.25rem;
  background-color: #2a2a2a;
  padding: 0.8rem;
  border-radius: 6px;
}

.attachments-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.attachments-header h4 {
  font-size: 0.8rem;
  margin: 0;
  color: #ccc;
  letter-spacing: 0.5px;
}

.add-attachment-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.4rem 0.8rem;
  border: none;
  background-color: rgba(138, 86, 255, 0.1);
  color: #8a56ff;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s ease;
}

.add-attachment-btn:hover {
  background-color: rgba(138, 86, 255, 0.2);
}

/* Assignee styles */
.assignee-selector {
  position: relative;
  width: 100%;
}

.assignees-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
}

.assignee-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.assignee-avatar {
  width: 28px;
  height: 28px;
  min-width: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #8a56ff;
  color: white;
  border-radius: 50%;
  font-size: 0.7rem;
  font-weight: 500;
}

.assignee-name {
  font-size: 0.85rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.no-assignee {
  font-size: 0.85rem;
  color: #777;
  font-style: italic;
  margin-bottom: 0.5rem;
}

.add-assignee-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
  background-color: rgba(138, 86, 255, 0.1);
  color: #8a56ff;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.15s ease;
}

.add-assignee-btn:hover {
  background-color: rgba(138, 86, 255, 0.2);
}

.assignee-dropdown {
  position: absolute;
  top: calc(100% + 4px);
  left: 0;
  width: 200px;
  max-height: 250px;
  background-color: #2a2a2a;
  border-radius: 6px;
  overflow: hidden;
  z-index: 10;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  display: flex;
  flex-direction: column;
  animation: fadeIn 0.15s ease;
}

.search-container {
  padding: 0.6rem;
  border-bottom: 1px solid #333;
}

.assignee-search {
  width: 100%;
  padding: 0.5rem;
  background-color: #252525;
  border: none;
  border-radius: 4px;
  color: #ccc;
  font-size: 0.8rem;
}

.assignee-search:focus {
  outline: none;
}

.assignee-list {
  overflow-y: auto;
  max-height: 200px;
  padding: 0.4rem 0;
}

.assignee-option {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 0.6rem;
  cursor: pointer;
  transition: all 0.15s ease;
}

.assignee-option:hover {
  background-color: #333;
}

.no-results, .loading-users {
  padding: 0.75rem;
  font-size: 0.8rem;
  color: #777;
  text-align: center;
}

.loading-users {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.spinner.small {
  width: 14px;
  height: 14px;
}

.empty-attachments {
  padding: 1rem;
  text-align: center;
  color: #777;
  font-size: 0.8rem;
  font-style: italic;
}

.attachments-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.attachment-item {
  display: flex;
  align-items: center;
  padding: 0.6rem;
  background-color: #252525;
  border-radius: 4px;
  transition: all 0.15s ease;
}

.attachment-item:hover {
  background-color: #2f2f2f;
}

.attachment-icon {
  margin-right: 0.75rem;
  width: 32px;
  height: 32px;
  min-width: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #333;
  border-radius: 4px;
  color: #aaa;
}

.attachment-details {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.1rem;
  overflow: hidden;
}

.attachment-name {
  font-size: 0.8rem;
  color: #ccc;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.attachment-size {
  font-size: 0.7rem;
  color: #777;
}

.download-btn {
  width: 32px;
  height: 32px;
  min-width: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: transparent;
  border-radius: 4px;
  color: #777;
  text-decoration: none;
  transition: all 0.15s ease;
}

.download-btn:hover {
  background-color: rgba(255, 255, 255, 0.1);
  color: #fff;
}

.uploading-indicator {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  background-color: rgba(138, 86, 255, 0.1);
  border-radius: 4px;
  margin-bottom: 0.75rem;
}

.uploading-indicator span {
  font-size: 0.8rem;
  color: #8a56ff;
}

.spinner {
  width: 18px;
  height: 18px;
  border: 2px solid transparent;
  border-top-color: #8a56ff;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.timer-display {
  display: flex;
  margin-bottom: 1.5rem;
  padding: 1.25rem;
  background-color: #252525;
  border-radius: 6px;
}

.time-spent {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.time-label {
  font-size: 0.8rem;
  color: #aaa;
  margin-bottom: 0.4rem;
  letter-spacing: 0.5px;
}

.time-value {
  font-size: 1.5rem;
  font-weight: 500;
  letter-spacing: 0.5px;
}

.timer-progress {
  width: 120px;
}

.progress-circle {
  position: relative;
  width: 100%;
  height: 100%;
}

.progress-circle svg {
  width: 100%;
  height: 100%;
  transform: rotate(-90deg);
}

.progress-text {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
}

.progress-text .percentage {
  font-size: 1.2rem;
  font-weight: 500;
  display: block;
}

.progress-text .label {
  font-size: 0.7rem;
  color: #999;
}

@media (max-width: 768px) {
  .timer-display {
    flex-direction: column;
    gap: 1rem;
  }
  
  .timer-progress {
    width: 100%;
    display: flex;
    justify-content: center;
  }
  
  .progress-circle {
    width: 100px;
    height: 100px;
  }
  
  .detail-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>