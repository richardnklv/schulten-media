<!-- resources/js/components/task-panel/TaskList.vue -->
<template>
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
  </div>
</template>

<script>
import TaskItem from './TaskItem.vue';
import draggable from 'vuedraggable';
import { useTaskStore } from '../../stores/taskStore';
import { computed } from 'vue';

export default {
  name: 'TaskList',
  components: {
    TaskItem,
    draggable
  },
  props: {
    tasks: {
      type: Array,
      default: () => []
    },
    searchQuery: {
      type: String,
      default: ''
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
    }
  },
  setup() {
    const taskStore = useTaskStore();
    return { taskStore };
  },
  computed: {
    // Get tasks filtered by search query
    displayedTasks() {
      return this.taskStore.searchTasks(this.searchQuery, this.tasks);
    },
    
    // Group tasks by priority for draggable sections
    groupedTasks() {
      return this.taskStore.getTasksGroupedByPriority(this.priorities, this.displayedTasks);
    },
    
    // Check if currently dragging (from store)
    isDragging() {
      return this.taskStore.isDragging;
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
    onDragStart(event) {
      const taskElement = event.item.__draggable_context.element;
      if (!taskElement || !taskElement.id) {
        console.error('Invalid task element:', taskElement);
        return;
      }
      
      // Use the store to track dragging state and task
      this.taskStore.startDragging(taskElement.id);
    },
    
    onDragEnd(event) {
      // If we don't have a dragged task in the store, just end the operation
      if (!this.taskStore.draggedTaskId) {
        this.taskStore.endDragging();
        return;
      }
      
      const containerEl = event.to;
      if (!containerEl || !containerEl.parentElement) {
        console.error('Invalid drop container');
        this.taskStore.endDragging();
        return;
      }
      
      // Get priority from container attribute
      const newPriority = containerEl.parentElement.getAttribute('data-priority');
      
      // Handle the drop in the store - this will update priority if needed
      this.taskStore.handlePriorityDrop(newPriority);
    },
    
    onDragChange(event) {
      // We handle priority changes in the onDragEnd method
      console.log('Task moved');
    }
  }
}
</script>

<style scoped>
.tasks-list {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-bottom: 1.25rem;
  overflow-y: auto;
  min-height: 200px;
  position: relative;
  z-index: 100; /* Higher than the selective overlay */
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
</style>