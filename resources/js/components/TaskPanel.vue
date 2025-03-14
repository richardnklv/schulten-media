<!-- resources/js/components/TaskPanel.vue -->
<template>
    <div class="task-panel">
      <div class="welcome-header">
        <h2>Hey {{ userName }}!</h2>
        <p class="subtitle">Focus on what matters most</p>
      </div>
      
      <div class="priority-tabs">
        <button 
          v-for="priority in priorities" 
          :key="priority.value"
          :class="['priority-tab', { active: selectedPriority === priority.value }, priority.value.toLowerCase().replace(' ', '-')]"
          @click="selectPriority(priority.value)"
        >
          {{ priority.label }}
        </button>
      </div>
      
      <div class="task-filter">
        <div class="status-filter">
          <button 
            v-for="status in statuses" 
            :key="status.value"
            :class="['status-btn', { active: selectedStatus === status.value }]"
            @click="selectStatus(status.value)"
          >
            {{ status.label }}
          </button>
        </div>
      </div>
      
      <div class="tasks-list">
        <div 
          v-for="task in tasks" 
          :key="task.id" 
          :class="['task-item', { 'selected': selectedTaskId === task.id }]"
          @click="$emit('select-task', task)"
        >
          <div class="task-item-content">
            <h3>{{ task.title }}</h3>
            <div class="task-meta">
              <span class="due-date">Due: {{ formatDate(task.due_date) }}</span>
            </div>
          </div>
          <span class="task-status-text" :class="task.status.toLowerCase().replace(' ', '-')">
            {{ task.status }}
          </span>
        </div>
        
        <div v-if="tasks.length === 0" class="empty-tasks">
          <div class="empty-illustration">
            <svg viewBox="0 0 24 24" width="64" height="64" stroke="currentColor" stroke-width="1" fill="none">
              <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
              <line x1="8" y1="12" x2="16" y2="12"></line>
              <line x1="8" y1="16" x2="16" y2="16"></line>
              <line x1="8" y1="8" x2="16" y2="8"></line>
            </svg>
          </div>
          <p>No tasks to display</p>
          <p class="empty-hint">Try selecting a different priority or status</p>
        </div>
      </div>
      
      <button class="add-task-btn" @click="$emit('add-task')">
        <span class="add-icon">
          <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="16"></line>
            <line x1="8" y1="12" x2="16" y2="12"></line>
          </svg>
        </span>
        <span>Add task</span>
      </button>
    </div>
  </template>
  
  <script>
  export default {
    name: 'TaskPanel',
    props: {
      userName: {
        type: String,
        default: 'there'
      },
      tasks: {
        type: Array,
        default: () => []
      },
      allTasks: {
        type: Array,
        default: () => []
      },
      selectedPriority: {
        type: String,
        required: true
      },
      selectedStatus: {
        type: String,
        default: 'all'
      },
      selectedTaskId: {
        type: [Number, String, null],
        default: null
      },
      priorities: {
        type: Array,
        default: () => [
          { value: 'Must be done', label: 'Must Do' },
          { value: 'Important', label: 'Important' },
          { value: 'Good to have', label: 'Nice to Have' }
        ]
      },
      statuses: {
        type: Array,
        default: () => [
          { value: 'all', label: 'All' },
          { value: 'To Do', label: 'To Do' },
          { value: 'In Progress', label: 'In Progress' },
          { value: 'Completed', label: 'Done' }
        ]
      }
    },
    emits: ['select-task', 'add-task', 'update-priority', 'update-status'],
    methods: {
      formatDate(dateString) {
        const options = { month: 'short', day: 'numeric' };
        return new Date(dateString).toLocaleDateString(undefined, options);
      },
      selectPriority(priority) {
        this.$emit('update-priority', priority);
        // Add a subtle animation effect
        const button = event.currentTarget;
        button.classList.add('btn-pulse');
        setTimeout(() => button.classList.remove('btn-pulse'), 300);
      },
      selectStatus(status) {
        this.$emit('update-status', status);
        // Add a subtle animation effect
        const button = event.currentTarget;
        button.classList.add('btn-pulse');
        setTimeout(() => button.classList.remove('btn-pulse'), 300);
      }
    }
  }
  </script>
  
  <style scoped>
  .task-panel {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    padding: 1.25rem;
    background-color: #1a1a1a;
    overflow-y: auto;
  }
  
  .welcome-header {
    margin-bottom: 1.5rem;
    text-align: center;
  }
  
  .welcome-header h2 {
    font-size: 1.5rem;
    font-weight: 500;
    margin-bottom: 0.25rem;
    letter-spacing: 0.5px;
  }
  
  .subtitle {
    color: #999;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
  }
  
  /* Zen UI/UX Priority Tabs */
  .priority-tabs {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1.25rem;
  }
  
  .priority-tab {
    flex: 1;
    padding: 0.5rem 0.4rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #252525;
    border: none;
    color: #aaa;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.8rem;
    transition: all 0.2s ease;
    letter-spacing: 0.5px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    position: relative;
  }
  
  .priority-tab:hover {
    background-color: #2a2a2a;
    color: #ddd;
  }
  
  .priority-tab.active {
    color: #fff;
    background-color: #2f2f2f;
    font-weight: 500;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
  }
  
  /* When one tab is active, dim the others */
  .priority-tabs:has(.priority-tab.active) .priority-tab:not(.active) {
    opacity: 0.6;
  }
  
  /* Fallback for browsers that don't support :has() */
  .priority-tab.active ~ .priority-tab:not(.active),
  .priority-tab:not(.active) ~ .priority-tab.active ~ .priority-tab:not(.active),
  .priority-tab:not(.active) + .priority-tab:not(.active) {
    opacity: 0.6;
  }
  
  /* Priority-specific styles - More zen and minimalist */
  .priority-tab.must-be-done {
    /* border-bottom: 2px solid #8a56ff; */
    font-weight: 500;
  }
  
  .priority-tab.must-be-done.active {
    background-color: rgba(138, 86, 255, 0.15);
  }
  
  .priority-tab.important {
    /* border-bottom: 2px solid #8a56ff; */
    opacity: 0.95;
  }
  
  .priority-tab.important.active {
    background-color: rgba(138, 86, 255, 0.1);
  }
  
  .priority-tab.good-to-have {
    /* border-bottom: 2px solid #8a56ff; */
    opacity: 0.9;
  }
  
  .priority-tab.good-to-have.active {
    background-color: rgba(138, 86, 255, 0.1);
  }
  
  /* Status filter - more zen and smaller */
  .task-filter {
    margin-bottom: 1.25rem;
  }
  
  .status-filter {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
    flex-wrap: wrap;
  }
  
  .status-btn {
    padding: 0.3rem 0.6rem;
    background-color: transparent;
    border: 1px solid #333;
    color: #999;
    border-radius: 20px;
    cursor: pointer;
    font-size: 0.7rem;
    transition: all 0.2s ease;
  }
  
  .status-btn:hover {
    border-color: #555;
    color: #ddd;
  }
  
  .status-btn.active {
    background-color: #333;
    color: #fff;
    border-color: #333;
  }
  
  /* When one status is active, dim the others */
  .status-filter:has(.status-btn.active) .status-btn:not(.active) {
    opacity: 0.7;
  }
  
  .tasks-list {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    margin-bottom: 1.25rem;
    overflow-y: auto;
    min-height: 200px;
  }
  
  .task-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 1rem;
    background-color: #252525;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    border-left: 3px solid transparent;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
  }
  
  .task-item:hover {
    background-color: #2a2a2a;
    transform: translateY(-1px);
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.2);
  }
  
  .task-item.selected {
    background-color: #2a2a2a;
    border-left: 3px solid #8a56ff;
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.2);
  }
  
  .task-item-content {
    flex: 1;
  }
  
  .task-item h3 {
    font-size: 0.9rem;
    font-weight: 400;
    margin-bottom: 0.2rem;
    color: #eee;
    line-height: 1.2;
  }
  
  .task-meta {
    font-size: 0.7rem;
    color: #999;
    display: flex;
    align-items: center;
  }
  
    .task-status-text {
    font-size: 0.65rem;
    font-weight: 500;
    padding: 0.15rem 0.4rem;
    border-radius: 4px;
    letter-spacing: 0.5px;
    margin-left: auto;
    white-space: nowrap;
  }
  
  .task-status-text.to-do {
    color: #fff;
    background-color: #6e00a5;
  }
  
  .task-status-text.in-progress {
    color: #fff;
    background-color: #3498db;
  }
  
  .task-status-text.completed {
    color: #aaa;
    background-color: rgba(46, 204, 113, 0.2);
  }
  
  .empty-tasks {
    text-align: center;
    padding: 2rem 1rem;
    color: #888;
    font-size: 0.9rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    background-color: #252525;
    border-radius: 8px;
    margin-top: 0.5rem;
  }
  
  .empty-illustration {
    color: #444;
    margin-bottom: 0.5rem;
  }
  
  .empty-hint {
    font-size: 0.75rem;
    color: #666;
    margin-top: -0.5rem;
  }
  
  .add-task-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    background-color: #8a56ff;
    border: none;
    color: white;
    padding: 0.75rem;
    border-radius: 8px;
    width: 100%;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.85rem;
    font-weight: 500;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 4px rgba(138, 86, 255, 0.3);
  }
  
  .add-task-btn:hover {
    background-color: #7445e0;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(138, 86, 255, 0.4);
  }
  
  .add-task-btn:active {
    transform: translateY(0);
    box-shadow: 0 1px 2px rgba(138, 86, 255, 0.4);
  }
  
  .add-icon {
    display: flex;
    align-items: center;
  }
  
  /* Animation */
  @keyframes pulse {
    0% {
      transform: scale(1);
    }
    50% {
      transform: scale(1.05);
    }
    100% {
      transform: scale(1);
    }
  }
  
  .btn-pulse {
    animation: pulse 0.3s ease-in-out;
  }
  </style>