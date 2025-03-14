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
        
        <div class="detail-item" v-if="task.assignee">
          <span class="detail-label">Assignee</span>
          <span class="detail-value">{{ task.assignee.name }}</span>
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
      isUpdating: false,
      isUploading: false,
      filesToUpload: [],
      availableStatuses: ['To Do', 'In Progress', 'Under Review', 'Completed']
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
      const dropdown = this.$el.querySelector('.status-dropdown');
      if (dropdown && !dropdown.contains(event.target)) {
        this.showStatusDropdown = false;
      }
    }
  },
  // Close dropdown when clicking outside
  mounted() {
    document.addEventListener('click', this.handleClickOutside);
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