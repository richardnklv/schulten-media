<!-- resources/js/components/task-panel/TaskFilter.vue -->
<template>
  <div class="task-filter">
    <div class="status-filter">
      <button 
        v-for="status in statuses" 
        :key="status.value"
        :class="['status-btn', { active: selectedStatus === status.value }]"
        @click="selectStatus(status.value, $event)"
      >
        {{ status.label }}
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'TaskFilter',
  props: {
    statuses: {
      type: Array,
      default: () => [
        { value: 'all', label: 'All' },
        { value: 'To Do', label: 'To Do' },
        { value: 'In Progress', label: 'In Progress' },
        { value: 'Completed', label: 'Done' }
      ]
    },
    selectedStatus: {
      type: String,
      default: 'all'
    }
  },
  methods: {
    selectStatus(status, event) {
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