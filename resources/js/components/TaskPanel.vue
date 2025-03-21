<!-- resources/js/components/TaskPanel.vue -->
<template>
  <div class="task-panel" :class="{ 'is-dragging': taskStore.isDragging }">
    <!-- Selective overlay when dragging - covers everything except priority tabs and task list -->
    <div v-if="taskStore.isDragging" class="selective-overlay">
      <!-- Cutouts for the priority tabs will be handled with CSS z-index -->
    </div>
    
    <WelcomeHeader :userName="userName" />
    
    <SearchContainer 
      :searchQuery="searchQuery" 
      @update:searchQuery="searchQuery = $event" 
      @search="handleSearch"
    />
    
    <PriorityTabs 
      :priorities="priorities" 
      :selectedPriority="selectedPriority" 
      :dragOverPriority="dragOverPriority"
      :isDragging="taskStore.isDragging"
      @update-priority="selectPriority"
      @drag-over-priority="handleDragOverPriority"
      @drag-leave-priority="handleDragLeavePriority"
      @drop-on-priority="handleDropOnPriority"
    />
    
    <TaskFilter 
      :statuses="statuses" 
      :selectedStatus="selectedStatus" 
      @update-status="selectStatus"
    />
    
    <TaskList 
      :tasks="tasks" 
      :searchQuery="searchQuery" 
      :selectedTaskId="selectedTaskId"
      :priorities="priorities"
      @select-task="$emit('select-task', $event)"
    />
    
    <AddTask @add-task="$emit('add-task')" />
  </div>
</template>

<script>
import WelcomeHeader from './task-panel/WelcomeHeader.vue';
import SearchContainer from './task-panel/SearchContainer.vue';
import PriorityTabs from './task-panel/PriorityTabs.vue';
import TaskFilter from './task-panel/TaskFilter.vue';
import TaskList from './task-panel/TaskList.vue';
import AddTask from './task-panel/AddTask.vue';
import { useTaskStore } from '../stores/taskStore';

export default {
  name: 'TaskPanel',
  components: {
    WelcomeHeader,
    SearchContainer,
    PriorityTabs,
    TaskFilter,
    TaskList,
    AddTask
  },
  setup() {
    const taskStore = useTaskStore();
    return { taskStore };
  },
  data() {
    return {
      searchQuery: '',
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
  methods: {
    handleSearch() {
      // Debounce logic could be added here if needed
      // For now, the computed property handles filtering in TaskList
    },
    selectPriority(priority) {
      this.$emit('update-priority', priority);
    },
    selectStatus(status) {
      this.$emit('update-status', status);
    },
    handleDragOverPriority(priority) {
      if (this.taskStore.isDragging) {
        this.dragOverPriority = priority;
      }
    },
    handleDragLeavePriority() {
      this.dragOverPriority = null;
    },
    handleDropOnPriority(priority) {
      const validPriorities = ['Must be done', 'Important', 'Good to have'];
      
      if (this.taskStore.draggedTaskId && validPriorities.includes(priority)) {
        // Let the store handle the priority update
        this.taskStore.handlePriorityDrop(priority);
      }
      
      this.dragOverPriority = null;
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
.is-dragging :deep(.welcome-header),
.is-dragging :deep(.search-container),
.is-dragging :deep(.task-filter) {
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