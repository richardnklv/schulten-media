<template>
  <div class="project-sidebar" :class="{ 'collapsed': isCollapsed }">
    <div class="sidebar-header">
      <!-- Toggle button - now inside the sidebar -->
      <button class="sidebar-toggle" @click="toggleSidebar">
        <svg v-if="isCollapsed" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none">
          <polyline points="9 18 15 12 9 6"></polyline>
        </svg>
        <svg v-else viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none">
          <polyline points="15 18 9 12 15 6"></polyline>
        </svg>
      </button>
    </div>
    
    <div class="user-profile">
      <div class="avatar">
        <span v-if="!user.avatar">{{ userInitials }}</span>
        <img v-else :src="user.avatar" :alt="user.name">
      </div>
      <div class="user-info" v-if="!isCollapsed">
        <h3>{{ user.name }}</h3>
        <p class="user-role">Project Manager</p>
      </div>
    </div>
    
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
            @click.stop="showEditProjectModal(project)"
            title="Edit project"
          >
            <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2" fill="none">
              <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
              <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
          </button>
        </li>
        <li class="project-item add-project" @click="showAddProjectModal" v-if="!isCollapsed">
          <span class="add-icon">+</span>
          <span class="project-name">Add Project</span>
        </li>
      </ul>
    </div>
    
    <!-- Add Project Modal -->
    <div class="modal-overlay" v-if="showProjectModal" @click.self="closeAddProjectModal">
      <div class="modal-content">
        <h2>Add New Project</h2>
        <form @submit.prevent="submitNewProject">
          <div class="form-group">
            <label for="projectTitle">Project Name</label>
            <input 
              type="text" 
              id="projectTitle" 
              v-model="newProject.title" 
              placeholder="Enter project name" 
              required
            >
          </div>
          
          <div class="form-group">
            <label for="projectDescription">Description</label>
            <textarea 
              id="projectDescription" 
              v-model="newProject.description" 
              placeholder="Enter project description"
              rows="3"
            ></textarea>
          </div>
          
          <div class="form-group">
            <label>Team Members</label>
            <div class="team-select">
              <div 
                v-for="member in team" 
                :key="member.id" 
                class="team-member-option"
                :class="{ 'selected': newProject.team_members.includes(member.id) }"
                @click="toggleTeamMember(member.id)"
              >
                <div class="team-avatar small">
                  <span>{{ getUserInitials(member.name) }}</span>
                </div>
                <span class="team-name">{{ member.name }}</span>
                <div v-if="newProject.team_members.includes(member.id)" class="selected-indicator">
                  <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none">
                    <polyline points="20 6 9 17 4 12"></polyline>
                  </svg>
                </div>
              </div>
            </div>
          </div>
          
          <div class="modal-actions">
            <button type="button" class="cancel-btn" @click="closeAddProjectModal">Cancel</button>
            <button type="submit" class="submit-btn" :disabled="isSubmitting">
              <span v-if="isSubmitting">Creating...</span>
              <span v-else>Create Project</span>
            </button>
          </div>
        </form>
      </div>
    </div>
    
    <!-- Edit Project Modal -->
    <div class="modal-overlay" v-if="showEditModal" @click.self="closeEditProjectModal">
      <div class="modal-content">
        <h2>Edit Project</h2>
        <form @submit.prevent="submitEditProject">
          <div class="form-group">
            <label for="editProjectTitle">Project Name</label>
            <input 
              type="text" 
              id="editProjectTitle" 
              v-model="editingProject.title" 
              placeholder="Enter project name" 
              required
            >
          </div>
          
          <div class="form-group">
            <label for="editProjectDescription">Description</label>
            <textarea 
              id="editProjectDescription" 
              v-model="editingProject.description" 
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
                v-for="member in team" 
                :key="member.id" 
                class="team-member-option"
                :class="{ 'selected': editingProject.team_members.includes(member.id) }"
                @click="toggleEditTeamMember(member.id)"
              >
                <div class="team-avatar small">
                  <span>{{ getUserInitials(member.name) }}</span>
                </div>
                <span class="team-name">{{ member.name }}</span>
                <div v-if="editingProject.team_members.includes(member.id)" class="selected-indicator">
                  <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none">
                    <polyline points="20 6 9 17 4 12"></polyline>
                  </svg>
                </div>
              </div>
            </div>
          </div>
          
          <div class="modal-actions">
            <button type="button" class="cancel-btn" @click="closeEditProjectModal">Cancel</button>
            <button type="submit" class="submit-btn" :disabled="isSubmitting">
              <span v-if="isSubmitting">Saving...</span>
              <span v-else>Save Changes</span>
            </button>
          </div>
        </form>
      </div>
    </div>
    
    <div class="sidebar-section">
      <h4 class="section-title" v-if="!isCollapsed">Team</h4>
      <ul class="team-list">
        <li v-for="member in team" :key="member.id" class="team-item">
          <div class="team-avatar">
            <span>{{ getUserInitials(member.name) }}</span>
          </div>
          <span class="team-name" v-if="!isCollapsed">{{ member.name }}</span>
          <span class="status-dot" :class="getRandomStatus()"></span>
        </li>
      </ul>
    </div>
    
    <div class="sidebar-section notifications-section" v-if="!isCollapsed">
      <h4 class="section-title">Notifications <span class="notification-badge" v-if="unreadCount > 0">{{ unreadCount }}</span></h4>
      <NotificationsPanel />
    </div>
    
    <div class="sidebar-footer" v-if="!isCollapsed">
      <button class="settings-btn">
        <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none">
          <circle cx="12" cy="12" r="3"></circle>
          <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
        </svg>
        <span>Settings</span>
      </button>
      <button class="logout-btn" @click="logout">
        <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
          <polyline points="16 17 21 12 16 7"></polyline>
          <line x1="21" y1="12" x2="9" y2="12"></line>
        </svg>
        <span>Logout</span>
      </button>
    </div>
  </div>
</template>

<script>
import projectService from '../../services/projectService';
import userService from '../../services/userService';
import authService from '../../services/authService';
import notificationService from '../../services/notificationService';
import NotificationsPanel from './NotificationsPanel.vue';

export default {
  name: 'ProjectSidebar',
  components: {
    NotificationsPanel
  },
  props: {
    user: {
      type: Object,
      required: true
    }
  },
  emits: ['toggle-sidebar', 'select-project'],
  data() {
    return {
      isCollapsed: false,
      projects: [],
      team: [],
      selectedProject: null,
      unreadCount: 0,
      projectColors: {
        // Default colors for projects
        1: '#8a56ff',
        2: '#3498db',
        3: '#e74c3c',
        4: '#2ecc71',
        5: '#f39c12'
      },
      isLoading: false,
      showProjectModal: false,
      isSubmitting: false,
      showEditModal: false,
      newProject: {
        title: '',
        description: '',
        team_members: []
      },
      editingProject: {
        id: null,
        title: '',
        description: '',
        team_members: []
      },
      notificationCheckInterval: null
    };
  },
  computed: {
    userInitials() {
      if (!this.user.name) return '';
      return this.getUserInitials(this.user.name);
    }
  },
  mounted() {
    this.fetchProjects();
    this.fetchTeam();
    
    // Update unread count initially
    this.updateUnreadCount();
    
    // Set interval to check for new notifications
    this.notificationCheckInterval = setInterval(() => {
      this.updateUnreadCount();
    }, 5000);
  },
  
  beforeUnmount() {
    // Clear interval when component is destroyed
    if (this.notificationCheckInterval) {
      clearInterval(this.notificationCheckInterval);
    }
  },
  
  methods: {
    updateUnreadCount() {
      this.unreadCount = notificationService.getUnreadCount();
    },
    toggleSidebar() {
      this.isCollapsed = !this.isCollapsed;
      this.$emit('toggle-sidebar', this.isCollapsed);
    },
    async fetchProjects() {
      try {
        this.isLoading = true;
        const response = await projectService.getAll();
        this.projects = response.data.data || response.data;
        
        // Set first project as selected by default if none is selected
        if (this.projects.length > 0 && !this.selectedProject) {
          this.selectedProject = this.projects[0].id;
        }
      } catch (error) {
        console.error('Error fetching projects:', error);
      } finally {
        this.isLoading = false;
      }
    },
    async fetchTeam() {
      try {
        this.isLoading = true;
        const response = await userService.getAll();
        this.team = response.data.data || response.data;
      } catch (error) {
        console.error('Error fetching team members:', error);
      } finally {
        this.isLoading = false;
      }
    },
    selectProject(id) {
      this.selectedProject = id;
      // Emit an event to update the parent component with selected project
      this.$emit('select-project', id);
    },
    showAddProjectModal() {
      this.showProjectModal = true;
      // Reset form when opening
      this.newProject = {
        title: '',
        description: '',
        team_members: []
      };
    },
    
    closeAddProjectModal() {
      this.showProjectModal = false;
    },
    
    async showEditProjectModal(project) {
      try {
        // Show loading indicator or some visual feedback
        this.isLoading = true;
        
        // Fetch the full project details to get team members
        const response = await projectService.get(project.id);
        const fullProject = response.data.data || response.data;
        
        this.editingProject = {
          id: project.id,
          title: project.title,
          description: project.description || '',
          // Get team members from the fetched project data
          team_members: fullProject.team_members ? 
            // Convert to array of IDs if it's an array of objects
            (Array.isArray(fullProject.team_members) ? 
              fullProject.team_members.map(member => typeof member === 'object' ? member.id : member) : 
              []) : 
            []
        };
        
        console.log('Editing project with team members:', this.editingProject.team_members);
        this.showEditModal = true;
      } catch (error) {
        console.error('Error fetching project details for editing:', error);
        alert('Could not load project details. Please try again.');
      } finally {
        this.isLoading = false;
      }
    },
    
    closeEditProjectModal() {
      this.showEditModal = false;
    },
    
    toggleEditTeamMember(userId) {
      const index = this.editingProject.team_members.indexOf(userId);
      if (index === -1) {
        // Add user to team if not already included
        this.editingProject.team_members.push(userId);
      } else {
        // Remove user from team if already included
        this.editingProject.team_members.splice(index, 1);
      }
    },
    
    async submitEditProject() {
      if (!this.editingProject.title.trim()) {
        alert('Project name is required');
        return;
      }
      
      try {
        this.isSubmitting = true;
        
        // Update project using the projectService
        const response = await projectService.update(this.editingProject.id, {
          title: this.editingProject.title,
          description: this.editingProject.description,
          team_members: this.editingProject.team_members
        });
        
        // Update the project in the projects list
        const updatedProject = response.data.data || response.data;
        const index = this.projects.findIndex(p => p.id === this.editingProject.id);
        
        if (index !== -1) {
          this.projects.splice(index, 1, updatedProject);
        }
        
        // Close the modal
        this.closeEditProjectModal();
        
      } catch (error) {
        console.error('Error updating project:', error);
        alert('Failed to update project. Please try again.');
      } finally {
        this.isSubmitting = false;
      }
    },
    
    toggleTeamMember(userId) {
      const index = this.newProject.team_members.indexOf(userId);
      if (index === -1) {
        // Add user to team if not already included
        this.newProject.team_members.push(userId);
      } else {
        // Remove user from team if already included
        this.newProject.team_members.splice(index, 1);
      }
    },
    
    async submitNewProject() {
      if (!this.newProject.title.trim()) {
        alert('Project name is required');
        return;
      }
      
      try {
        this.isSubmitting = true;
        
        // Create project using the projectService
        const response = await projectService.create({
          title: this.newProject.title,
          description: this.newProject.description,
          team_members: this.newProject.team_members
        });
        
        // Add the new project to the projects list with its ID from response
        const newProject = response.data.data || response.data;
        this.projects.unshift(newProject);
        
        // Select the newly created project
        this.selectedProject = newProject.id;
        this.$emit('select-project', newProject.id);
        
        // Close the modal
        this.closeAddProjectModal();
        
      } catch (error) {
        console.error('Error creating project:', error);
        alert('Failed to create project. Please try again.');
      } finally {
        this.isSubmitting = false;
      }
    },
    logout() {
      authService.logout().then(() => {
        authService.removeToken();
        window.location.href = '/login';
      }).catch(error => {
        console.error('Error logging out:', error);
      });
    },
    getUserInitials(name) {
      if (!name) return '';
      return name.split(' ').map(n => n[0]).join('');
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
    },
    getRandomStatus() {
      // For demo purposes, assign random status to team members
      const statuses = ['online', 'offline', 'away'];
      return statuses[Math.floor(Math.random() * statuses.length)];
    }
  }
};
</script>

<style scoped>
.project-sidebar {
  height: 100%;
  background-color: #1a1a1a;
  color: #fff;
  display: flex;
  flex-direction: column;
  padding: 1.25rem;
  overflow-y: auto;
  overflow-x: hidden;
  position: relative;
  transition: all 0.3s ease;
  width: 100%;
}

.project-sidebar.collapsed {
  padding: 1.25rem 0;
  width: 60px;
  align-items: center;
  background-color: rgba(26, 26, 26, 0.95);
  backdrop-filter: blur(5px);
}

.sidebar-header {
  display: flex;
  justify-content: flex-end;
  width: 100%;
  margin-bottom: 1rem;
}

.sidebar-toggle {
  width: 28px;
  height: 28px;
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

.project-sidebar.collapsed .sidebar-header {
  justify-content: center;
}

.user-profile {
  display: flex;
  align-items: center;
  gap: 0.8rem;
  margin-bottom: 2rem;
  padding-bottom: 1.25rem;
  border-bottom: 1px solid #333;
  transition: all 0.3s ease;
}

.project-sidebar.collapsed .user-profile {
  justify-content: center;
  margin-bottom: 1.5rem;
}

.avatar {
  width: 40px;
  height: 40px;
  min-width: 40px;
  border-radius: 50%;
  background-color: #8a56ff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 500;
  color: #fff;
  font-size: 1rem;
  transition: all 0.3s ease;
}

.project-sidebar.collapsed .avatar {
  width: 36px;
  height: 36px;
  min-width: 36px;
}

.avatar img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
}

.user-info h3 {
  font-size: 0.9rem;
  font-weight: 500;
  margin: 0;
  margin-bottom: 0.2rem;
}

.user-role {
  font-size: 0.7rem;
  color: #aaa;
  margin: 0;
}

.sidebar-section {
  margin-bottom: 2rem;
  width: 100%;
}

.project-sidebar.collapsed .sidebar-section {
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

.notification-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 18px;
  height: 18px;
  border-radius: 9px;
  background-color: #8a56ff;
  color: white;
  font-size: 0.65rem;
  font-weight: 600;
  margin-left: 0.5rem;
  padding: 0 0.25rem;
}

.project-list, .team-list {
  list-style: none;
  padding: 0;
  margin: 0;
  width: 100%;
}

.project-sidebar.collapsed .project-list,
.project-sidebar.collapsed .team-list {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.project-item, .team-item {
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

.project-sidebar.collapsed .project-item:hover .project-color,
.project-sidebar.collapsed .team-item:hover .team-avatar {
  transform: scale(1.1);
  box-shadow: 0 0 10px rgba(138, 86, 255, 0.3);
}

.project-item.active {
  background-color: rgba(138, 86, 255, 0.1);
}

.project-sidebar.collapsed .project-item.active .project-color {
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

.project-sidebar.collapsed .project-color {
  width: 12px;
  height: 12px;
  margin-right: 0;
}

.project-name, .team-name {
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

.team-avatar {
  width: 24px;
  height: 24px;
  min-width: 24px;
  border-radius: 50%;
  background-color: #2a2a2a;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.7rem;
  color: #ddd;
  margin-right: 0.75rem;
  transition: all 0.3s ease;
}

.project-sidebar.collapsed .team-avatar {
  width: 28px;
  height: 28px;
  min-width: 28px;
  margin-right: 0;
  font-size: 0.8rem;
  background-color: #333;
}

.status-dot {
  width: 8px;
  height: 8px;
  min-width: 8px;
  border-radius: 50%;
  margin-left: 0.5rem;
  transition: all 0.3s ease;
}

.project-sidebar.collapsed .status-dot {
  position: absolute;
  top: 6px;
  right: 10px;
  width: 6px;
  height: 6px;
  margin-left: 0;
}

.status-dot.online {
  background-color: #2ecc71;
}

.status-dot.offline {
  background-color: #999;
}

.status-dot.away {
  background-color: #f39c12;
}

.notifications-section {
  margin-top: auto;
}

.project-sidebar.collapsed .notifications-section {
  display: none;
}

.notifications-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 1rem;
  color: #777;
  text-align: center;
}

.empty-icon {
  margin-bottom: 0.5rem;
  opacity: 0.5;
}

.notifications-empty p {
  font-size: 0.75rem;
  margin: 0;
}

.sidebar-footer {
  margin-top: 1rem;
  padding-top: 1.25rem;
  border-top: 1px solid #333;
  display: flex;
  justify-content: space-between;
}

.settings-btn, .logout-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: none;
  border: none;
  color: #aaa;
  font-size: 0.8rem;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 4px;
  transition: all 0.2s ease;
}

.settings-btn:hover, .logout-btn:hover {
  background-color: #252525;
  color: #fff;
}

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
</style>