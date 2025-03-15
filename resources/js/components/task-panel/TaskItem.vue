<template>
  <div 
    :class="['task-item', { 'selected': isSelected, 'expanded': isSelected && hasAssignees }]"
    @click="$emit('select')"
  >
    <div class="task-item-content">
      <h3>{{ task.title }}</h3>
      <div class="task-meta">
        <span class="due-date">Due: {{ formattedDueDate }}</span>
        
        <!-- Assignee mini avatars (visible when task is not expanded) -->
        <div v-if="hasAssignees" class="assignee-mini-avatars" :class="{ 'hidden': isSelected && hasAssignees }">
          <!-- Single assignee case -->
          <div v-if="task.assignee_id && !task.assignees" class="assignee-mini-avatar" :title="assigneeName">
            {{ getInitials(assigneeName) }}
          </div>
          
          <!-- Multiple assignees case - show up to 3 -->
          <template v-else>
            <div 
              v-for="(assignee, index) in task.assignees ? task.assignees.slice(0, 3) : []" 
              :key="assignee.id" 
              class="assignee-mini-avatar"
              :style="{ zIndex: 10 - index, marginLeft: index > 0 ? '-8px' : '0' }" 
              :title="assignee.name"
            >
              {{ getInitials(assignee.name) }}
            </div>
            
            <!-- If there are more than 3 assignees, show a "+X" indicator -->
            <div 
              v-if="task.assignees && task.assignees.length > 3" 
              class="assignee-mini-avatar more-indicator"
              :title="`${task.assignees.length - 3} more assignees`"
            >
              +{{ task.assignees.length - 3 }}
            </div>
          </template>
        </div>
      </div>
    </div>
    <span class="task-status-text" :class="statusClass">
      {{ task.status }}
    </span>
    
    <!-- Assignees section that appears when task is selected -->
    <div v-if="isSelected && hasAssignees" class="assignees-section">
      <div class="assignee-list">
        <div v-if="task.assignee_id && !task.assignees" class="assignee-item">
          <div class="assignee-avatar">
            <span>{{ getInitials(assigneeName) }}</span>
          </div>
          <span class="assignee-name">{{ assigneeName }}</span>
        </div>
        <div v-for="assignee in task.assignees" :key="assignee.id" class="assignee-item">
          <div class="assignee-avatar">
            <span>{{ getInitials(assignee.name) }}</span>
          </div>
          <span class="assignee-name">{{ assignee.name }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'TaskItem',
  props: {
    task: {
      type: Object,
      required: true
    },
    isSelected: {
      type: Boolean,
      default: false
    }
  },
  emits: ['select'],
  computed: {
    formattedDueDate() {
      const options = { month: 'short', day: 'numeric' };
      return new Date(this.task.due_date).toLocaleDateString(undefined, options);
    },
    statusClass() {
      return this.task.status.toLowerCase().replace(' ', '-');
    },
    assigneeName() {
      // Check if assignee info is available
      if (this.task.assignee && this.task.assignee.name) {
        return this.task.assignee.name;
      } else if (this.task.assignee_name) {
        return this.task.assignee_name;
      }
      return null;
    },
    hasAssignees() {
      return this.assigneeName || 
             (this.task.assignees && this.task.assignees.length > 0);
    }
  },
  methods: {
    getInitials(name) {
      if (!name) return '';
      return name.split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase();
    }
  }
}
</script>

<style scoped>
.task-item {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 1rem;
  background-color: #252525;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  border-left: 3px solid transparent;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
  margin-bottom: 0.75rem;
  position: relative;
  /* Ensure task items don't appear above modals */
  z-index: 1;
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

.task-item.expanded {
  padding-bottom: 1rem;
}

.task-item-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 0; /* Helps with text-overflow in children */
  max-width: calc(100% - 180px); /* Further increased space for avatars and status */
}

.task-item h3 {
  font-size: 0.9rem;
  font-weight: 400;
  margin-bottom: 0.2rem;
  color: #eee;
  line-height: 1.2;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.task-meta {
  font-size: 0.7rem;
  color: #999;
  display: flex;
  align-items: center;
  flex-wrap: wrap;
}

.due-date {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.task-status-text {
  font-size: 0.65rem;
  font-weight: 500;
  padding: 0.15rem 0.4rem;
  border-radius: 4px;
  letter-spacing: 0.5px;
  margin-left: auto;
  white-space: nowrap;
  position: relative;
  /* Use a local z-index to position within task item */
  z-index: 1; 
  min-width: 5rem; /* Increased to fit longer statuses */
  text-align: center;
}

.task-status-text.to-do {
  color: #fff;
  background-color: #ff5a5f;
}

.task-status-text.in-progress {
  color: #fff;
  background-color: #3498db;
}

.task-status-text.under-review {
  color: #fff;
  background-color: #f39c12;
  font-size: 0.6rem; /* Slightly smaller for this longer text */
}

.task-status-text.completed {
  color: #aaa;
  background-color: rgba(46, 204, 113, 0.2);
}

/* Mini avatars in non-expanded task */
.assignee-mini-avatars {
  display: flex;
  align-items: center;
  position: absolute;
  top: 50%;
  right: 7rem; /* Further increased padding for longer status text */
  transform: translateY(-50%);
  transition: opacity 0.2s ease;
  /* Lower z-index to ensure it doesn't appear above modals */
  z-index: 1;
}

.assignee-mini-avatars.hidden {
  display: none;
}

.assignee-mini-avatar {
  width: 22px;
  height: 22px;
  min-width: 22px;
  border-radius: 50%;
  background-color: #8a56ff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.7rem;
  font-weight: 500;
  color: white;
  transition: all 0.2s ease;
  border: 1px solid #2a2a2a;
  /* Lower z-index for avatars stacked within task item */
  position: relative;
  z-index: 1;
}

.assignee-mini-avatar.more-indicator {
  background-color: #333;
  font-size: 0.65rem;
}

/* Styles for the assignees section */
.assignees-section {
  width: 100%;
  margin-top: 0.7rem;
  padding-top: 0.7rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  opacity: 0;
  transform: translateY(-5px);
  animation: fadeIn 0.3s ease forwards;
  transition: all 0.3s ease;
}

@keyframes fadeIn {
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.assignee-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.assignee-item {
  display: flex;
  align-items: center;
  padding: 0.25rem 0.5rem;
  border-radius: 20px;
  background-color: #333;
  font-size: 0.7rem;
}

.assignee-avatar {
  width: 20px;
  height: 20px;
  min-width: 20px;
  border-radius: 50%;
  background-color: #8a56ff;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 0.4rem;
  font-size: 0.6rem;
  font-weight: 500;
  color: white;
}

.assignee-name {
  white-space: nowrap;
  color: #eee;
}
</style>