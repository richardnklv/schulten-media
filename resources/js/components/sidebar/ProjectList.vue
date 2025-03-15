<template>
  <div class="sidebar-section">
    <h4 class="section-title" v-if="!isCollapsed">Projects</h4>
    <ul class="project-list">
      <li v-for="project in projects" 
          :key="project.id" 
          :class="['project-item', { 'active': selectedProject === project.id }]">
        <div class="project-content" @click="selectProject(project.id)">
          <span class="project-color" :style="{ backgroundColor: getProjectColor(project.id) }"></span>
          <span class="project-name" v-if="!isCollapsed">{{ project.title }}</span>
        </div>
        <button 
          v-if="!isCollapsed" 
          class="edit-project-btn" 
          @click.stop="$emit('edit-project', project)"
          title="Edit project"
        >
          <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2" fill="none">
            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
          </svg>
        </button>
      </li>
      <li class="project-item add-project" @click="$emit('add-project')" v-if="!isCollapsed">
        <span class="add-icon">+</span>
        <span class="project-name">Add Project</span>
      </li>
    </ul>
  </div>
</template>

<script>
export default {
  name: 'ProjectList',
  props: {
    projects: {
      type: Array,
      required: true
    },
    selectedProject: {
      type: Number,
      default: null
    },
    isCollapsed: {
      type: Boolean,
      required: true
    }
  },
  emits: ['select-project', 'edit-project', 'add-project'],
  data() {
    return {
      projectColors: {
        // Default colors for projects
        1: '#8a56ff',
        2: '#3498db',
        3: '#e74c3c',
        4: '#2ecc71',
        5: '#f39c12'
      }
    };
  },
  methods: {
    selectProject(id) {
      this.$emit('select-project', id);
    },
    getProjectColor(id) {
      // Return a color based on project ID, or generate one if it doesn't exist
      if (this.projectColors[id]) {
        return this.projectColors[id];
      }
      
      // Generate a color if one doesn't exist
      const colors = ['#8a56ff', '#3498db', '#e74c3c', '#2ecc71', '#f39c12', '#9b59b6', '#1abc9c', '#34495e'];
      const color = colors[id % colors.length];
      this.projectColors[id] = color;
      return color;
    }
  }
};
</script>

<style scoped>
.sidebar-section {
  margin-bottom: 2rem;
  width: 100%;
}

:deep(.project-sidebar.collapsed) .sidebar-section {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.section-title {
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: #777;
  margin-bottom: 0.8rem;
  font-weight: 500;
  display: flex;
  align-items: center;
}

.project-list {
  list-style: none;
  padding: 0;
  margin: 0;
  width: 100%;
}

:deep(.project-sidebar.collapsed) .project-list {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.project-item {
  display: flex;
  align-items: center;
  padding: 0.5rem;
  font-size: 0.85rem;
  border-radius: 4px;
  transition: all 0.2s ease;
  position: relative;
  justify-content: space-between;
}

.project-content {
  display: flex;
  align-items: center;
  flex: 1;
  cursor: pointer;
}

.project-item:hover, .team-item:hover {
  background-color: rgba(255, 255, 255, 0.05);
}

.edit-project-btn {
  width: 22px;
  height: 22px;
  min-width: 22px;
  border-radius: 4px;
  background-color: transparent;
  border: none;
  color: #777;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  opacity: 0;
  transition: all 0.2s ease;
}

.project-item:hover .edit-project-btn {
  opacity: 1;
}

.edit-project-btn:hover {
  background-color: rgba(255, 255, 255, 0.1);
  color: #fff;
}

:deep(.project-sidebar.collapsed) .project-item:hover .project-color {
  transform: scale(1.1);
  box-shadow: 0 0 10px rgba(138, 86, 255, 0.3);
}

.project-item.active {
  background-color: rgba(138, 86, 255, 0.1);
}

:deep(.project-sidebar.collapsed) .project-item.active .project-color {
  box-shadow: 0 0 5px 2px rgba(138, 86, 255, 0.5);
}

.project-color {
  width: 10px;
  height: 10px;
  min-width: 10px;
  border-radius: 50%;
  margin-right: 0.75rem;
  transition: all 0.3s ease;
}

:deep(.project-sidebar.collapsed) .project-color {
  width: 12px;
  height: 12px;
  margin-right: 0;
}

.project-name {
  flex: 1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.add-project {
  color: #aaa;
}

.add-icon {
  width: 10px;
  height: 10px;
  min-width: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  margin-right: 0.75rem;
}
</style>