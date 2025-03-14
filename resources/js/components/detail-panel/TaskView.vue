<template>
  <div class="task-detail-view">
    <div class="task-detail-header">
      <button class="back-btn" @click="$emit('close-task')" v-if="isMobile">
        <span>&larr;</span>
      </button>
      <h2>{{ task.title }}</h2>
      <div class="task-actions">
        <button class="timer-btn" @click="toggleTimer">
          <span class="timer-icon" v-if="!isTimerRunning">
            <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none">
              <circle cx="12" cy="12" r="10"></circle>
              <polygon points="10 8 16 12 10 16 10 8"></polygon>
            </svg>
          </span>
          <span class="timer-icon" v-else>
            <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="10" y1="15" x2="10" y2="9"></line>
              <line x1="14" y1="15" x2="14" y2="9"></line>
            </svg>
          </span>
          {{ isTimerRunning ? 'Pause' : (timeSpent > 0 ? 'Resume' : 'Start Focus') }}
        </button>
        <!-- Change this button in the template -->
        <button 
          class="complete-btn" 
          v-if="task.status !== 'Completed'"
          @click="completeTask"
        >
          <span class="complete-icon">
            <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none">
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
          </span>
          Done
        </button>
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
            <span class="detail-value" :class="'status-' + task.status.toLowerCase().replace(' ', '-')">
              {{ task.status }}
            </span>
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
      </div>
      
      <div class="comments-section">
        <h3>Comments</h3>
        <div v-if="comments.length === 0" class="empty-comments">
          No comments yet
        </div>
        <div v-else class="comments-list">
          <div v-for="comment in comments" :key="comment.id" class="comment">
            <div class="comment-header">
              <span class="comment-author">{{ comment.user.name }}</span>
              <span class="comment-date">{{ formatDateTime(comment.created_at) }}</span>
            </div>
            <p class="comment-content">{{ comment.content }}</p>
          </div>
        </div>
        
        <div class="add-comment">
          <textarea 
            v-model="newComment" 
            placeholder="Add a comment..." 
            class="comment-input"
          ></textarea>
          <button class="add-comment-btn" @click="addComment" :disabled="!newComment.trim()">
            Comment
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
// No longer need to import services as parent will handle API calls

export default {
  name: 'TaskView',
  props: {
    task: {
      type: Object,
      required: true
    },
    comments: {
      type: Array,
      default: () => []
    },
    isMobile: {
      type: Boolean,
      default: false
    }
  },
  emits: ['close-task', 'complete-task', 'add-comment', 'timer-update'],
  data() {
    return {
      newComment: '',
      timeSpent: 0,
      isTimerRunning: false,
      timerInterval: null,
      goalTime: 6 * 60 * 60 // 6 hours in seconds
    }
  },
  computed: {
    progressPercentage() {
      return Math.min(Math.round((this.timeSpent / this.goalTime) * 100), 100);
    },
    progressOffset() {
      // Circle circumference formula: 2πr = 2 * Math.PI * 45 ≈ 282.7
      const circumference = 2 * Math.PI * 45;
      return circumference - (circumference * this.progressPercentage / 100);
    }
  },
  methods: {
    toggleTimer() {
      if (this.isTimerRunning) {
        // Pause timer
        clearInterval(this.timerInterval);
        this.isTimerRunning = false;
      } else {
        // Start timer
        this.isTimerRunning = true;
        this.timerInterval = setInterval(() => {
          this.timeSpent += 1; // Increment by 1 second
          this.$emit('timer-update', this.timeSpent);
        }, 1000);
      }
    },
    formatTime(seconds) {
      const hours = Math.floor(seconds / 3600);
      const minutes = Math.floor((seconds % 3600) / 60);
      const secs = seconds % 60;
      
      return `${hours}h ${minutes}m ${secs}s`;
    },
    formatDate(dateString) {
      const options = { year: 'numeric', month: 'short', day: 'numeric' };
      return new Date(dateString).toLocaleDateString(undefined, options);
    },
    formatDateTime(dateTimeString) {
      const options = { 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      };
      return new Date(dateTimeString).toLocaleString(undefined, options);
    },
    completeTask() {
      // Just emit the event, let parent component handle the API call
      this.$emit('complete-task', this.task);
    },
    addComment() {
      if (!this.newComment.trim()) return;
      
      // Just emit the event with comment content, let parent handle the API call
      this.$emit('add-comment', this.newComment);
      this.newComment = '';
    },
    resetTimer() {
      this.timeSpent = 0;
      this.isTimerRunning = false;
      clearInterval(this.timerInterval);
    }
  },
  beforeUnmount() {
    // Clear any running timers
    if (this.timerInterval) {
      clearInterval(this.timerInterval);
    }
  }
}
</script>

<style scoped>
/* Task Detail View */
.task-detail-view {
  padding: 1.25rem;
  overflow-y: auto;
}

.task-detail-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.25rem;
}

.back-btn {
  background: none;
  border: none;
  color: #fff;
  font-size: 1.2rem;
  cursor: pointer;
  margin-right: 0.5rem;
}

.task-detail-header h2 {
  font-size: 1.25rem;
  font-weight: 500;
  letter-spacing: 0.5px;
  flex: 1;
  margin: 0;
}

.task-actions {
  display: flex;
  gap: 0.5rem;
}

.timer-btn, .complete-btn {
  display: flex;
  align-items: center;
  gap: 0.3rem;
  padding: 0.4rem 0.75rem;
  border-radius: 4px;
  font-size: 0.75rem;
  cursor: pointer;
  border: none;
  transition: all 0.15s ease;
  letter-spacing: 0.5px;
}

.timer-icon, .complete-icon {
  display: flex;
  align-items: center;
}

.timer-btn {
  background-color: #252525;
  color: #fff;
}

.timer-btn:hover {
  background-color: #333;
}

.complete-btn {
  background-color: #8a56ff;
  color: #fff;
}

.complete-btn:hover {
  background-color: #7445e0;
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

/* Comments Section */
.comments-section {
  background-color: #252525;
  padding: 1.25rem;
  border-radius: 6px;
}

.comments-section h3 {
  font-size: 0.9rem;
  margin-bottom: 0.8rem;
  margin-top: 0;
  font-weight: 500;
  letter-spacing: 0.5px;
}

.empty-comments {
  padding: 0.8rem;
  color: #999;
  text-align: center;
  font-style: italic;
  font-size: 0.8rem;
}

.comments-list {
  display: flex;
  flex-direction: column;
  gap: 0.8rem;
  margin-bottom: 1.25rem;
}

.comment {
  background-color: #2a2a2a;
  padding: 0.8rem;
  border-radius: 6px;
}

.comment-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.4rem;
  font-size: 0.7rem;
}

.comment-author {
  font-weight: 500;
}

.comment-date {
  color: #999;
}

.comment-content {
  font-size: 0.8rem;
  line-height: 1.4;
  color: #ccc;
  margin: 0;
}

.add-comment {
  margin-top: 0.8rem;
}

.comment-input {
  width: 100%;
  background-color: #2a2a2a;
  border: 1px solid #333;
  color: #fff;
  padding: 0.6rem;
  border-radius: 6px;
  margin-bottom: 0.6rem;
  resize: vertical;
  height: 80px;
  font-family: inherit;
  font-size: 0.8rem;
}

.add-comment-btn {
  background-color: #8a56ff;
  color: #fff;
  border: none;
  padding: 0.4rem 0.8rem;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.75rem;
  float: right;
  letter-spacing: 0.5px;
}

.add-comment-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
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