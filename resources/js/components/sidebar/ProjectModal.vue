<template>
  <div class="modal-overlay" v-if="showModal" @click.self="$emit('close')">
    <div class="modal-content">
      <h2>{{ isEditing ? 'Edit Project' : 'Add New Project' }}</h2>
      <form @submit.prevent="submitForm">
        <div class="form-group">
          <label :for="isEditing ? 'editProjectTitle' : 'projectTitle'">Project Name</label>
          <input 
            type="text" 
            :id="isEditing ? 'editProjectTitle' : 'projectTitle'" 
            v-model="projectData.title" 
            placeholder="Enter project name" 
            required
          >
        </div>
        
        <div class="form-group">
          <label :for="isEditing ? 'editProjectDescription' : 'projectDescription'">Description</label>
          <textarea 
            :id="isEditing ? 'editProjectDescription' : 'projectDescription'" 
            v-model="projectData.description" 
            placeholder="Enter project description"
            rows="3"
          ></textarea>
        </div>
        
        <div class="form-group">
          <label>Team Members</label>
          <div v-if="isLoading" class="team-loading">
            <div class="loading-spinner"></div>
            <span>Loading team members...</span>
          </div>
          <div v-else class="team-select">
            <div 
              v-for="member in teamMembers" 
              :key="member.id" 
              class="team-member-option"
              :class="{ 'selected': projectData.team_members.includes(member.id) }"
              @click="toggleTeamMember(member.id)"
            >
              <div class="team-avatar small">
                <span>{{ getUserInitials(member.name) }}</span>
              </div>
              <span class="team-name">{{ member.name }}</span>
              <div v-if="projectData.team_members.includes(member.id)" class="selected-indicator">
                <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none">
                  <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
              </div>
            </div>
          </div>
        </div>
        
        <div class="modal-actions">
          <button type="button" class="cancel-btn" @click="$emit('close')">Cancel</button>
          <button type="submit" class="submit-btn" :disabled="isSubmitting">
            <span v-if="isSubmitting">{{ isEditing ? 'Saving...' : 'Creating...' }}</span>
            <span v-else>{{ isEditing ? 'Save Changes' : 'Create Project' }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ProjectModal',
  props: {
    showModal: {
      type: Boolean,
      required: true
    },
    isEditing: {
      type: Boolean,
      default: false
    },
    project: {
      type: Object,
      default: () => ({
        id: null,
        title: '',
        description: '',
        team_members: []
      })
    },
    teamMembers: {
      type: Array,
      required: true
    },
    isSubmitting: {
      type: Boolean,
      default: false
    },
    isLoading: {
      type: Boolean,
      default: false
    }
  },
  emits: ['close', 'submit'],
  data() {
    return {
      projectData: {
        id: null,
        title: '',
        description: '',
        team_members: []
      }
    };
  },
  watch: {
    project: {
      handler(newVal) {
        this.projectData = {
          id: newVal.id,
          title: newVal.title || '',
          description: newVal.description || '',
          team_members: [...(newVal.team_members || [])]
        };
      },
      immediate: true,
      deep: true
    }
  },
  methods: {
    getUserInitials(name) {
      if (!name) return '';
      return name.split(' ').map(n => n[0]).join('');
    },
    toggleTeamMember(userId) {
      const index = this.projectData.team_members.indexOf(userId);
      if (index === -1) {
        // Add user to team if not already included
        this.projectData.team_members.push(userId);
      } else {
        // Remove user from team if already included
        this.projectData.team_members.splice(index, 1);
      }
    },
    submitForm() {
      if (!this.projectData.title.trim()) {
        alert('Project name is required');
        return;
      }
      
      this.$emit('submit', { ...this.projectData });
    }
  }
};
</script>

<style scoped>
/* Modal styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  backdrop-filter: blur(3px);
}

.modal-content {
  background-color: #222;
  border-radius: 8px;
  padding: 1.5rem;
  width: 100%;
  max-width: 450px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
}

.modal-content h2 {
  margin-top: 0;
  margin-bottom: 1.25rem;
  font-size: 1.25rem;
  color: #fff;
  border-bottom: 1px solid #333;
  padding-bottom: 0.75rem;
}

.form-group {
  margin-bottom: 1.25rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-size: 0.85rem;
  color: #ccc;
}

.form-group input,
.form-group textarea {
  width: 100%;
  padding: 0.75rem;
  background-color: #2a2a2a;
  border: 1px solid #333;
  border-radius: 4px;
  color: #fff;
  font-size: 0.9rem;
  transition: all 0.2s ease;
}

.form-group input:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #8a56ff;
  box-shadow: 0 0 0 2px rgba(138, 86, 255, 0.2);
}

.team-select {
  max-height: 200px;
  overflow-y: auto;
  background-color: #2a2a2a;
  border: 1px solid #333;
  border-radius: 4px;
}

.team-member-option {
  display: flex;
  align-items: center;
  padding: 0.6rem 0.75rem;
  cursor: pointer;
  transition: all 0.2s ease;
  border-bottom: 1px solid #333;
}

.team-member-option:last-child {
  border-bottom: none;
}

.team-member-option:hover {
  background-color: #333;
}

.team-member-option.selected {
  background-color: rgba(138, 86, 255, 0.15);
}

.selected-indicator {
  color: #8a56ff;
  margin-left: auto;
  display: flex;
  align-items: center;
  justify-content: center;
}

.team-avatar.small {
  width: 28px;
  height: 28px;
  min-width: 28px;
  font-size: 0.75rem;
  margin-right: 0.75rem;
  background-color: #2a2a2a;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #ddd;
}

.team-loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 1.5rem;
  color: #aaa;
  font-size: 0.85rem;
}

.loading-spinner {
  width: 24px;
  height: 24px;
  border: 2px solid #333;
  border-top: 2px solid #8a56ff;
  border-radius: 50%;
  margin-bottom: 0.75rem;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  margin-top: 1.5rem;
}

.cancel-btn,
.submit-btn {
  padding: 0.6rem 1.25rem;
  border-radius: 4px;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.cancel-btn {
  background-color: transparent;
  border: 1px solid #444;
  color: #ccc;
}

.cancel-btn:hover {
  background-color: #2a2a2a;
  border-color: #555;
}

.submit-btn {
  background-color: #8a56ff;
  border: none;
  color: white;
}

.submit-btn:hover {
  background-color: #7548e3;
  transform: translateY(-1px);
}

.submit-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.team-name {
  flex: 1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>