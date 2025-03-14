<template>
  <div class="task-detail-header">
    <button class="back-btn" @click="$emit('close')" v-if="isMobile">
      <span>&larr;</span>
    </button>
    <h2>{{ task.title }}</h2>
    <div class="task-actions">
      <button class="timer-btn" @click="$emit('toggle-timer')">
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
      <button 
        class="complete-btn" 
        v-if="task.status !== 'Completed'"
        @click="$emit('complete')"
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
</template>

<script>
export default {
  name: 'DetailHeader',
  props: {
    task: {
      type: Object,
      required: true
    },
    isMobile: {
      type: Boolean,
      default: false
    },
    isTimerRunning: {
      type: Boolean,
      default: false
    },
    timeSpent: {
      type: Number,
      default: 0
    }
  },
  emits: ['close', 'complete', 'toggle-timer']
}
</script>

<style scoped>
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
</style>