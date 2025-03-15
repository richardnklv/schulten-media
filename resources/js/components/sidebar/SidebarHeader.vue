<template>
  <div class="sidebar-header">
    <!-- Toggle button - now inside the sidebar -->
    <button class="sidebar-toggle" @click="$emit('toggle')">
      <svg v-if="isCollapsed" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none">
        <polyline points="9 18 15 12 9 6"></polyline>
      </svg>
      <svg v-else viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none">
        <polyline points="15 18 9 12 15 6"></polyline>
      </svg>
    </button>
  </div>
</template>

<script>
export default {
  name: 'SidebarHeader',
  props: {
    isCollapsed: {
      type: Boolean,
      required: true
    }
  },
  emits: ['toggle']
};
</script>

<style scoped>
.sidebar-header {
  display: flex;
  justify-content: flex-end;
  width: 100%;
  margin-bottom: 1rem;
}

.sidebar-toggle {
  width: 28px;
  height: 28px;
  min-width: 28px;
  background-color: #333;
  border: none;
  border-radius: 50%;
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.sidebar-toggle:hover {
  background-color: #8a56ff;
  transform: scale(1.05);
}

:deep(.project-sidebar.collapsed) .sidebar-header {
  justify-content: center;
}
</style>