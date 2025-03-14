<template>
    <div class="task-list-container">
      <div class="task-list-header">
        <h2>My Tasks</h2>
        <button class="refresh-btn" @click="fetchTasks">
          Refresh
        </button>
      </div>
      
      <div v-if="loading" class="loading">
        Loading tasks...
      </div>
      
      <div v-else-if="error" class="error">
        {{ error }}
      </div>
      
      <div v-else-if="tasks.length === 0" class="empty-tasks">
        No tasks available
      </div>
      
      <div v-else class="tasks">
        <div 
          v-for="task in tasks" 
          :key="task.id" 
          class="task-item"
          @click="selectTask(task)"
        >
          <div class="task-status" :class="'status-' + task.status.toLowerCase().replace(' ', '-')"></div>
          <div class="task-content">
            <h3>{{ task.title }}</h3>
            <p class="task-description">{{ task.description.substring(0, 100) }}{{ task.description.length > 100 ? '...' : '' }}</p>
            <div class="task-meta">
              <span class="due-date">Due: {{ formatDate(task.due_date) }}</span>
              <span class="priority" :class="'priority-' + task.priority.toLowerCase().replace(' ', '-')">
                {{ task.priority }}
              </span>
            </div>
          </div>
        </div>
      </div>
  
      <div v-if="selectedTask" class="task-detail-panel">
        <task-view 
          :task="selectedTask"
          :comments="taskComments"
          @close-task="selectedTask = null"
          @complete-task="handleCompleteTask"
          @add-comment="handleAddComment"
          @task-updated="handleTaskUpdated"
        />
      </div>
    </div>
  </template>
  
  <script>
  import taskService from '../services/taskService';
  import commentService from '../services/commentService';
  import TaskView from './detail-panel/TaskView.vue';
  
  export default {
    name: 'TaskList',
    components: {
      TaskView
    },
    data() {
      return {
        tasks: [],
        selectedTask: null,
        taskComments: [],
        loading: false,
        error: null
      }
    },
    mounted() {
      this.fetchTasks();
    },
    methods: {
      async fetchTasks() {
        try {
          this.loading = true;
          const response = await taskService.getAll();
          this.tasks = response.data.data || response.data;
        } catch (error) {
          console.error('Error fetching tasks:', error);
          this.error = 'Failed to load tasks. Please try again.';
        } finally {
          this.loading = false;
        }
      },
      async selectTask(task) {
        this.selectedTask = task;
        this.fetchTaskComments(task.id);
      },
      async fetchTaskComments(taskId) {
        try {
          const response = await commentService.getForTask(taskId);
          this.taskComments = response.data.data || response.data;
        } catch (error) {
          console.error('Error fetching comments:', error);
        }
      },
      async handleCompleteTask(task) {
        // Update the task status locally
        const taskIndex = this.tasks.findIndex(t => t.id === task.id);
        if (taskIndex !== -1) {
          this.tasks[taskIndex].status = 'Completed';
        }
        
        // Refresh the task list
        this.fetchTasks();
      },
      async handleAddComment(comment) {
        // Refresh comments after adding a new one
        if (this.selectedTask) {
          this.fetchTaskComments(this.selectedTask.id);
        }
      },
      handleTaskUpdated(task) {
        // Update the task in the list
        const taskIndex = this.tasks.findIndex(t => t.id === task.id);
        if (taskIndex !== -1) {
          this.tasks[taskIndex] = task;
        }
      },
      formatDate(dateString) {
        const options = { year: 'numeric', month: 'short', day: 'numeric' };
        return new Date(dateString).toLocaleDateString(undefined, options);
      }
    }
  }
  </script>
  
  <style scoped>
  .task-list-container {
    display: flex;
    flex-direction: column;
    height: 100%;
  }
  
  .task-list-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
  }
  
  .task-list-header h2 {
    font-size: 1.25rem;
    margin: 0;
  }
  
  .refresh-btn {
    background-color: #8a56ff;
    color: white;
    border: none;
    padding: 0.4rem 0.8rem;
    border-radius: 4px;
    cursor: pointer;
  }
  
  .loading, .error, .empty-tasks {
    padding: 2rem;
    text-align: center;
    color: #aaa;
  }
  
  .error {
    color: #e74c3c;
  }
  
  .tasks {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    overflow-y: auto;
  }
  
  .task-item {
    display: flex;
    background-color: #252525;
    border-radius: 6px;
    overflow: hidden;
    cursor: pointer;
    transition: transform 0.1s ease;
  }
  
  .task-item:hover {
    transform: translateY(-2px);
  }
  
  .task-status {
    width: 4px;
  }
  
  .task-status.status-to-do {
    background-color: #999;
  }
  
  .task-status.status-in-progress {
    background-color: #3498db;
  }
  
  .task-status.status-under-review {
    background-color: #9b59b6;
  }
  
  .task-status.status-completed {
    background-color: #2ecc71;
  }
  
  .task-content {
    padding: 1rem;
    flex: 1;
  }
  
  .task-content h3 {
    font-size: 0.9rem;
    margin: 0 0 0.5rem 0;
    font-weight: 500;
  }
  
  .task-description {
    font-size: 0.8rem;
    color: #aaa;
    margin: 0 0 0.5rem 0;
    line-height: 1.4;
  }
  
  .task-meta {
    display: flex;
    justify-content: space-between;
    font-size: 0.75rem;
  }
  
  .due-date {
    color: #999;
  }
  
  .priority {
    font-weight: 500;
  }
  
  .priority-must-be-done {
    color: #e74c3c;
  }
  
  .priority-important {
    color: #f39c12;
  }
  
  .priority-good-to-have {
    color: #2ecc71;
  }
  
  .task-detail-panel {
    position: fixed;
    top: 0;
    right: 0;
    width: 400px;
    height: 100vh;
    background-color: #1e1e1e;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
    z-index: 100;
    overflow-y: auto;
  }
  
  @media (max-width: 768px) {
    .task-detail-panel {
      width: 100%;
    }
  }
  </style>