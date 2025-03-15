<!-- resources/js/components/task-panel/AddTask.vue -->
<template>
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
</template>

<script>
export default {
  name: 'AddTask',
  emits: ['add-task']
}
</script>

<style scoped>
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
</style>