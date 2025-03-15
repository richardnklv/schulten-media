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
  data() {
    return {
      isDragging: false,
      draggedTask: null
    }
  },
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
    onDragStart(event) {
      this.isDragging = true;
      this.draggedTask = event.item.__draggable_context.element;
      
      // Store the original priority for comparison later
      if (this.draggedTask) {
        this.draggedTask._originalPriority = this.draggedTask.priority;
        console.log('Started dragging task:', this.draggedTask.id, 'with priority:', this.draggedTask.priority);
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
          console.log('TaskList: Updating task', this.draggedTask.id, 'priority from', 
                     this.draggedTask._originalPriority, 'to', priorityGroup);
          
          // Make a clean copy of just the required data
          const taskUpdate = {
            id: this.draggedTask.id,
            priority: priorityGroup
          };
          
          // Send to parent for API update
          this.$emit('update-task-priority', taskUpdate);
        }
      }
      
      this.isDragging = false;
      this.draggedTask = null;
      this.$emit('drag-end');
    },
    
    onDragChange(event) {
      // We handle priority changes in the onDragEnd method for simplicity
      console.log('Task moved');
    },
    
    updateTaskPriority(task, newPriority) {
      // Ensure we have a task object with an id
      if (!task || !task.id) {
        console.error('Cannot update priority: Invalid task object', task);
        return;
      }
      
      console.log('TaskList is updating priority for task ID:', task.id, 'New priority:', newPriority);
      
      // Show full task object for debugging
      console.log('Original task object:', JSON.stringify(task));
      
      // Create a copy of the task with the new priority
      const updatedTask = { 
        ...task, 
        priority: newPriority 
      };
      
      console.log('Updated task object to emit:', JSON.stringify(updatedTask));
      
      // Emit the update event to parent component
      this.$emit('update-task-priority', updatedTask);
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