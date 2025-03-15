<!-- resources/js/components/task-panel/PriorityTabs.vue -->
<template>
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
      @click="selectPriority(priority.value, $event)"
      @dragover.prevent="handleDragOverPriority(priority.value)"
      @dragleave.prevent="handleDragLeavePriority"
      @drop.prevent="handleDropOnPriority(priority.value)"
    >
      {{ priority.label }}
    </button>
  </div>
</template>

<script>
export default {
  name: 'PriorityTabs',
  props: {
    priorities: {
      type: Array,
      default: () => [
        { value: 'all', label: 'All' },
        { value: 'Must be done', label: 'Must Do' },
        { value: 'Important', label: 'Important' },
        { value: 'Good to have', label: 'Nice to Have' }
      ]
    },
    selectedPriority: {
      type: String,
      default: 'all'
    },
    dragOverPriority: {
      type: String,
      default: null
    },
    isDragging: {
      type: Boolean,
      default: false
    }
  },
  methods: {
    selectPriority(priority, event) {
      this.$emit('update-priority', priority);
      // Add a subtle animation effect
      const button = event.currentTarget;
      button.classList.add('btn-pulse');
      setTimeout(() => button.classList.remove('btn-pulse'), 300);
    },
    handleDragOverPriority(priority) {
      if (this.isDragging) {
        this.$emit('drag-over-priority', priority);
      }
    },
    handleDragLeavePriority() {
      this.$emit('drag-leave-priority');
    },
    handleDropOnPriority(priority) {
      this.$emit('drop-on-priority', priority);
    }
  }
}
</script>

<style scoped>
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

.drop-target {
  background-color: rgba(138, 86, 255, 0.15) !important;
  transform: scale(1.03);
  box-shadow: 0 0 15px rgba(138, 86, 255, 0.3) !important;
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