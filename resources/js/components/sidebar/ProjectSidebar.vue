<template>
  <div class="project-sidebar" :class="{ 'collapsed': isCollapsed }">
    <SidebarHeader :isCollapsed="isCollapsed" @toggle="toggleSidebar" />
    <UserProfile :user="user" :isCollapsed="isCollapsed" />
    
    <ProjectList 
      :projects="projects" 
      :selectedProject="selectedProject" 
      :isCollapsed="isCollapsed"
      @select-project="selectProject"
      @edit-project="showEditProjectModal"
      @add-project="showAddProjectModal"
    />
    
    <!-- Project Modals -->
    <ProjectModal 
      :showModal="showProjectModal" 
      :isEditing="false"
      :project="newProject"
      :teamMembers="team"
      :isSubmitting="isSubmitting"
      :isLoading="isLoading"
      @close="closeAddProjectModal"
      @submit="submitNewProject"
    />
    
    <ProjectModal 
      :showModal="showEditModal" 
      :isEditing="true"
      :project="editingProject"
      :teamMembers="team"
      :isSubmitting="isSubmitting"
      :isLoading="isLoading"
      @close="closeEditProjectModal"
      @submit="submitEditProject"
    />
    
    <TeamList :team="team" :isCollapsed="isCollapsed" />
    
    <div class="sidebar-section notifications-section" v-if="!isCollapsed">
      <h4 class="section-title">Notifications <span class="notification-badge" v-if="unreadCount > 0">{{ unreadCount }}</span></h4>
      <NotificationsPanel />
    </div>
    
    <SidebarFooter :isCollapsed="isCollapsed" />
  </div>
</template>

<script>
import projectService from '../../services/projectService';
import userService from '../../services/userService';
import notificationService from '../../services/notificationService';

import SidebarHeader from './SidebarHeader.vue';
import UserProfile from './UserProfile.vue';
import ProjectList from './ProjectList.vue';
import TeamList from './TeamList.vue';
import NotificationsPanel from './NotificationsPanel.vue';
import SidebarFooter from './SidebarFooter.vue';
import ProjectModal from './ProjectModal.vue';

export default {
  name: 'ProjectSidebar',
  components: {
    SidebarHeader,
    UserProfile,
    ProjectList,
    TeamList,
    NotificationsPanel,
    SidebarFooter,
    ProjectModal
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
    
    async submitEditProject(projectData) {
      try {
        this.isSubmitting = true;
        
        // Update project using the projectService
        const response = await projectService.update(projectData.id, {
          title: projectData.title,
          description: projectData.description,
          team_members: projectData.team_members
        });
        
        // Update the project in the projects list
        const updatedProject = response.data.data || response.data;
        const index = this.projects.findIndex(p => p.id === projectData.id);
        
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
    
    async submitNewProject(projectData) {
      try {
        this.isSubmitting = true;
        
        // Create project using the projectService
        const response = await projectService.create({
          title: projectData.title,
          description: projectData.description,
          team_members: projectData.team_members
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

.notifications-section {
  margin-top: auto;
}

.project-sidebar.collapsed .notifications-section {
  display: none;
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
</style>