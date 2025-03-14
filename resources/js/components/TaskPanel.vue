<!-- resources/js/components/TaskPanel.vue -->
<template>
  <div class="task-panel" :class="{ 'is-dragging': isDragging }">
    <!-- Selective overlay when dragging - covers everything except priority tabs and task list -->
    <div v-if="isDragging" class="selective-overlay">
      <!-- Cutouts for the priority tabs will be handled with CSS z-index -->
    </div>
    
    <div class="welcome-header">
      <h2>Hey {{ userName }}!</h2>
      <p class="subtitle">Focus on what matters most</p>
    </div>
    
    <div class="search-container">
      <div class="search-input-wrapper">
        <input 
          type="text" 
          v-model="searchQuery" 
          placeholder="Filter tasks..." 
          class="zen-search-input"
          @input="handleSearch"
        />
        <button v-if="searchQuery" @click="clearSearch" class="clear-search-btn">
          <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2" fill="none">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>
        <svg v-else class="search-icon" viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2" fill="none">
          <circle cx="11" cy="11" r="8"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
      </div>
    </div>
    
    <div class="priority-tabs">
      <button 
        v-for="priority in priorities" 
        :key="priority.value"
        :class="[
          'priority-tab', 
          { active: selectedPriority === priority.value }, 
          { 'drop-target': dragOverPriority === priority.value },
          priority.value.toLowerCase().replace(' ', '-')
        ]"
        @click="selectPriority(priority.value)"
        @dragover.prevent="handleDragOverPriority(priority.value)"
        @dragleave.prevent="handleDragLeavePriority"
        @drop.prevent="handleDropOnPriority(priority.value)"
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
    
    <div class="tasks-list" :class="{ 'dragging': isDragging }">
      <!-- Group tasks by priority -->
      <div v-for="(groupTasks, priority, index) in groupedTasks" :key="priority" :data-priority="priority">
        <!-- Priority separator line -->
        <div class="priority-separator">
          <span class="priority-label">{{ priority }}</span>
        </div>
        
        <!-- Draggable container for tasks -->
        <draggable
          v-model="groupedTasksModel[priority]"
          :id="index"
          :data-priority="priority"
          group="tasks"
          item-key="id"
          ghost-class="ghost-task"
          chosen-class="dragging-task"
          @start="onDragStart"
          @end="onDragEnd"
          @change="onDragChange"
        >
          <template #item="{ element: task }">
            <TaskItem 
              :task="task" 
              :isSelected="selectedTaskId === task.id"
              @select="$emit('select-task', task)"
            />
          </template>
        </draggable>
      </div>
      
      <div v-if="displayedTasks.length === 0" class="empty-tasks">
        <div class="empty-illustration">
          <svg viewBox="0 0 24 24" width="64" height="64" stroke="currentColor" stroke-width="1" fill="none">
            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
            <line x1="8" y1="12" x2="16" y2="12"></line>
            <line x1="8" y1="16" x2="16" y2="16"></line>
            <line x1="8" y1="8" x2="16" y2="8"></line>
          </svg>
        </div>
        <p v-if="searchQuery">No matching tasks found</p>
        <p v-else>No tasks to display</p>
        <p v-if="searchQuery" class="empty-hint">Try a different search term</p>
        <p v-else class="empty-hint">Try selecting a different priority or status</p>
      </div>
      
      <!-- No overlay here in the tasks list -->
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
import TaskItem from './task-panel/TaskItem.vue';
import draggable from 'vuedraggable';

export default {
  name: 'TaskPanel',
  components: {
    TaskItem,
    draggable
  },
  data() {
    return {
      searchQuery: '',
      filteredTasks: [],
      isDragging: false,
      draggedTask: null,
      dragOverPriority: null
    }
  },
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
      default: 'all'
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
        { value: 'all', label: 'All' },
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
  emits: ['select-task', 'add-task', 'update-priority', 'update-status', 'update-task-priority'],
  computed: {
    displayedTasks() {
      if (!this.searchQuery.trim()) {
        return this.tasks;
      }
      
      // Remove multiple spaces and convert to lowercase for more flexible search
      const query = this.searchQuery.toLowerCase().replace(/\s+/g, ' ').trim();
      // Also create a version with spaces removed entirely for even more flexible matching
      const noSpacesQuery = query.replace(/\s+/g, '');
      
      return this.tasks.filter(task => {
        const titleLower = task.title.toLowerCase();
        const descLower = task.description ? task.description.toLowerCase() : '';
        const titleNoSpaces = titleLower.replace(/\s+/g, '');
        const descNoSpaces = descLower.replace(/\s+/g, '');
        
        // Try multiple matching approaches for better user experience
        return titleLower.includes(query) || 
               descLower.includes(query) ||
               titleNoSpaces.includes(noSpacesQuery) ||
               descNoSpaces.includes(noSpacesQuery);
      });
    },
    
    // Group tasks by priority for draggable sections
    groupedTasks() {
      // First get the filtered tasks from the search
      const filteredTasks = this.displayedTasks;
      
      // Group them by priority
      const grouped = {};
      
      // Make sure all priority groups exist even if empty
      this.priorities.forEach(priority => {
        if (priority.value !== 'all') {
          grouped[priority.value] = [];
        }
      });
      
      // Populate with tasks
      filteredTasks.forEach(task => {
        if (task.priority && task.priority !== 'all') {
          if (!grouped[task.priority]) {
            grouped[task.priority] = [];
          }
          grouped[task.priority].push(task);
        }
      });
      
      return grouped;
    },
    
    // This is used for the v-model in draggable components
    groupedTasksModel: {
      get() {
        return this.groupedTasks;
      },
      set(value) {
        // We handle changes in the onDragChange method
        // This is a required computed prop for draggable to work with v-model
      }
    }
  },
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
    },
    handleSearch() {
      // Debounce logic could be added here if needed
      // For now, the computed property handles filtering
    },
    clearSearch() {
      this.searchQuery = '';
      // Add a subtle animation effect
      const searchInput = document.querySelector('.zen-search-input');
      searchInput.classList.add('cleared');
      setTimeout(() => searchInput.classList.remove('cleared'), 300);
    },
    
    // Drag and drop handlers
    onDragStart(event) {
      this.isDragging = true;
      this.draggedTask = event.item.__draggable_context.element;
    },
    
    onDragEnd() {
      this.isDragging = false;
      this.draggedTask = null;
      this.dragOverPriority = null;
    },
    
    onDragStart(event) {
      this.isDragging = true;
      this.draggedTask = event.item.__draggable_context.element;
      
      // Store the original priority for comparison later
      if (this.draggedTask) {
        this.draggedTask._originalPriority = this.draggedTask.priority;
      }
    },
    
    onDragEnd(event) {
      // If we have a dragged task and it's in a new container, update priority
      if (this.draggedTask) {
        const containerEl = event.to;
        // The parent element of the draggable container has our priority data
        const priorityGroup = containerEl.parentElement.getAttribute('data-priority');
        
        // Check if priority changed
        if (priorityGroup && this.draggedTask._originalPriority !== priorityGroup) {
          this.updateTaskPriority(this.draggedTask, priorityGroup);
        }
      }
      
      this.isDragging = false;
      this.draggedTask = null;
      this.dragOverPriority = null;
    },
    
    onDragChange(event) {
      // We handle priority changes in the onDragEnd method for simplicity
      console.log('Task moved');
    },
    
    handleDragOverPriority(priority) {
      if (this.isDragging && this.draggedTask) {
        this.dragOverPriority = priority;
      }
    },
    
    handleDragLeavePriority() {
      this.dragOverPriority = null;
    },
    
    handleDropOnPriority(priority) {
      if (this.draggedTask && this.draggedTask.priority !== priority) {
        this.updateTaskPriority(this.draggedTask, priority);
      }
      this.dragOverPriority = null;
    },
    
    updateTaskPriority(task, newPriority) {
      // Create a copy of the task with the new priority
      const updatedTask = { ...task, priority: newPriority };
      
      // Emit the update event to parent component
      this.$emit('update-task-priority', updatedTask);
      
      // Optionally, update local task data if needed
      const index = this.tasks.findIndex(t => t.id === task.id);
      if (index !== -1) {
        this.tasks[index].priority = newPriority;
      }
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
  margin-bottom: 1rem;
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

/* Zen Search Bar */
.search-container {
  margin-bottom: 0.75rem;
  display: flex;
  justify-content: center;
  width: 100%;
}

.search-input-wrapper {
  position: relative;
  width: 100%;
}

.zen-search-input {
  width: 100%;
  padding: 0.5rem 2.2rem 0.5rem 2.2rem;
  background-color: rgba(40, 40, 40, 0.5);
  border: 1px solid #2c2c2c;
  border-radius: 6px;
  color: #eee;
  font-size: 0.8rem;
  transition: all 0.3s ease;
  letter-spacing: 0.3px;
}

.zen-search-input:focus {
  outline: none;
  background-color: rgba(42, 42, 42, 0.6);
  border-color: #333;
}

.zen-search-input::placeholder {
  color: #666;
  font-style: italic;
  font-size: 0.75rem;
  letter-spacing: 0.5px;
}

.search-icon {
  position: absolute;
  left: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  color: #555;
  opacity: 0.7;
  pointer-events: none;
}

.clear-search-btn {
  position: absolute;
  right: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #555;
  opacity: 0.7;
  cursor: pointer;
  transition: all 0.2s ease;
}

.clear-search-btn:hover {
  color: #999;
}

.zen-search-input.cleared {
  animation: clearSubtle 0.4s ease;
}

@keyframes clearSubtle {
  0% {
    opacity: 1;
  }
  50% {
    opacity: 0.8;
  }
  100% {
    opacity: 1;
  }
}

/* Zen UI/UX Priority Tabs */
.priority-tabs {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1.25rem;
  position: relative;
  z-index: 110; /* Higher than the selective overlay */
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
.priority-tab.all {
  font-weight: 500;
}

.priority-tab.all.active {
  background-color: rgba(138, 86, 255, 0.2);
  box-shadow: 0 2px 6px rgba(138, 86, 255, 0.2);
}

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
  position: relative; /* For positioning relative to overlay */
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
  position: relative;
  z-index: 110; /* Higher than the selective overlay */
}

/* Priority separator styling - Zen UI/UX */
.priority-separator {
  position: relative;
  margin: 1.25rem 0 0.75rem;
  text-align: left;
  height: 1px;
  background: linear-gradient(90deg, rgba(138, 86, 255, 0.1), rgba(138, 86, 255, 0.15), rgba(138, 86, 255, 0.1));
  opacity: 0.7;
}

.priority-label {
  position: absolute;
  top: -9px;
  left: 50%;
  transform: translateX(-50%);
  padding: 0 0.75rem;
  background-color: #1a1a1a;
  font-size: 0.65rem;
  color: #999;
  letter-spacing: 0.6px;
  text-transform: uppercase;
  font-weight: 500;
}

.first-in-group {
  margin-top: 0.3rem;
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

/* Grey out add button when dragging */
.is-dragging .add-task-btn {
  opacity: 0.3;
  pointer-events: none;
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

/* Drag and drop styles */
.dragging {
  cursor: grabbing !important;
}

.ghost-task {
  opacity: 0.5;
  background-color: #333 !important;
  box-shadow: 0 0 10px rgba(138, 86, 255, 0.2) !important;
}

.dragging-task {
  cursor: grabbing;
}

.drop-target {
  background-color: rgba(138, 86, 255, 0.15) !important;
  transform: scale(1.03);
  box-shadow: 0 0 15px rgba(138, 86, 255, 0.3) !important;
}

.selective-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  pointer-events: none;
  z-index: 100;
  animation: fadeIn 0.2s ease;
}

/* Additional selectors to handle greying out specific parts */
.is-dragging .welcome-header,
.is-dragging .search-container,
.is-dragging .task-filter {
  opacity: 0.3;
  position: relative;
  z-index: 95; /* Below the overlay */
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}
</style>