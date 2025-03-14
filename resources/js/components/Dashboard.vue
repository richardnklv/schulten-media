<!-- resources/js/components/Dashboard.vue -->
<template>
  <div class="zen-app">
    <div class="zen-container" :class="{ 'detail-open': selectedTask && isMobile }">
      <!-- Project Sidebar -->
      <div class="project-sidebar" :class="{ 'collapsed': isSidebarCollapsed }">
        <!-- Toggle button -->
        <button class="sidebar-toggle" @click="toggleSidebar">
          <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none">
            <path d="M16 18l-6-6 6-6" v-if="!isSidebarCollapsed"></path>
            <path d="M9 18l6-6-6-6" v-else></path>
          </svg>
        </button>
        
        <!-- Sidebar content -->
        <div class="sidebar-content" v-if="!isSidebarCollapsed">
          <div class="sidebar-header">
            <h3>Projects</h3>
            <button class="add-btn" @click="showAddProjectForm = true">
              <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2" fill="none">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
              </svg>
            </button>
          </div>
          
          <div class="projects-list">
            <div 
              v-for="project in projects" 
              :key="project.id" 
              :class="['project-item', { 'active': selectedProjectId === project.id }]"
              @click="selectProject(project)"
            >
              {{ project.title }}
            </div>
            
            <div v-if="projects.length === 0" class="empty-projects">
              No projects available
            </div>
          </div>
          
          <div class="sidebar-section">
            <h4>Notifications</h4>
            <div class="empty-notifications">
              No new notifications
            </div>
          </div>
        </div>
      </div>
      
      <!-- Resizable Splitter for Project Sidebar -->
      <ResizableSplitter 
        v-if="!isMobile && !isSidebarCollapsed" 
        @resize="handleProjectSidebarResize" 
      />
      
      <!-- Task Panel (now middle panel) -->
      <div class="middle-panel" :class="{ 'hidden': selectedTask && isMobile }" :style="{ width: middlePanelWidth + 'px' }">
        <TaskPanel 
          :user-name="user.name"
          :tasks="filteredTasksByProject"
          :all-tasks="tasks"
          :selected-priority="selectedPriority"
          :selected-status="selectedStatus"
          :selected-task-id="selectedTask ? selectedTask.id : null"
          :priorities="priorities"
          :statuses="statuses"
          @select-task="selectTask"
          @update-priority="updatePriority"
          @update-status="updateStatus"
          @add-task="showAddTaskForm = true"
        />
      </div>
      
      <!-- Resizable Splitter for Task Panel -->
      <ResizableSplitter 
        v-if="!isMobile && (!selectedTask || !isMobile)" 
        @resize="handleTaskPanelResize" 
      />
      
      <!-- Right Panel - Task Details & Progress -->
      <div class="right-panel">
        <DetailPanel 
          :selected-task="selectedTask"
          :comments="taskComments"
          :is-mobile="isMobile"
          @close-task="selectedTask = null"
          @complete-task="markAsCompleted"
          @add-comment="addNewComment"
        />
      </div>
    </div>
    
    <!-- Add Task Modal -->
    <div class="modal" v-if="showAddTaskForm">
      <div class="modal-content">
        <div class="modal-header">
          <h2>Add New Task</h2>
          <button class="close-modal-btn" @click="showAddTaskForm = false">&times;</button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="addTask">
            <div class="form-group">
              <label for="task-title">Title</label>
              <input id="task-title" type="text" v-model="newTask.title" required>
            </div>
            
            <div class="form-group">
              <label for="task-description">Description</label>
              <textarea id="task-description" v-model="newTask.description"></textarea>
            </div>
            
            <div class="form-group">
              <label for="task-project">Project</label>
              <select id="task-project" v-model="newTask.project_id" required>
                <option v-for="project in projects" :key="project.id" :value="project.id">
                  {{ project.title }}
                </option>
              </select>
            </div>
            
            <div class="form-group">
              <label for="task-priority">Priority</label>
              <select id="task-priority" v-model="newTask.priority" required>
                <option v-for="priority in priorities" :key="priority.value" :value="priority.value">
                  {{ priority.label }}
                </option>
              </select>
            </div>
            
            <div class="form-group">
              <label for="task-due-date">Due Date</label>
              <input id="task-due-date" type="date" v-model="newTask.due_date" required>
            </div>
            
            <div class="form-group">
              <label for="task-assignee">Assignee</label>
              <select id="task-assignee" v-model="newTask.assignee_id">
                <option value="">Unassigned</option>
                <option v-for="user in users" :key="user.id" :value="user.id">
                  {{ user.name }}
                </option>
              </select>
            </div>
            
            <div class="form-actions">
              <button type="button" class="cancel-btn" @click="showAddTaskForm = false">Cancel</button>
              <button type="submit" class="submit-btn">Create Task</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    
    <!-- Add Project Modal -->
    <div class="modal" v-if="showAddProjectForm">
      <div class="modal-content">
        <div class="modal-header">
          <h2>Add New Project</h2>
          <button class="close-modal-btn" @click="showAddProjectForm = false">&times;</button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="addProject">
            <div class="form-group">
              <label for="project-title">Title</label>
              <input id="project-title" type="text" v-model="newProject.title" required>
            </div>
            
            <div class="form-group">
              <label for="project-description">Description</label>
              <textarea id="project-description" v-model="newProject.description"></textarea>
            </div>
            
            <div class="form-actions">
              <button type="button" class="cancel-btn" @click="showAddProjectForm = false">Cancel</button>
              <button type="submit" class="submit-btn">Create Project</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
// Add this import at the top with your other imports
import authService from '../services/authService.js';
import apiClient from '../services/api.js';
import TaskPanel from './TaskPanel.vue';
import DetailPanel from './DetailPanel.vue';
import ResizableSplitter from './ResizableSplitter.vue';
import taskService from '../services/taskService.js';
import projectService from '../services/projectService.js';
import userService from '../services/userService.js';
import commentService from '../services/commentService.js';
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';

export default {
  name: 'Dashboard',
  components: {
    TaskPanel,
    DetailPanel,
    ResizableSplitter
  },
  setup() {
    // State
    const tasks = ref([]);
    const projects = ref([]);
    const users = ref([]);
    
    const selectedTask = ref(null);
    const selectedPriority = ref('Must be done');
    const selectedStatus = ref('all');
    const selectedProjectId = ref(null);
    
    const taskComments = ref([]);
    const showAddTaskForm = ref(false);
    const showAddProjectForm = ref(false);
    const isMobile = ref(false);
    const isSidebarCollapsed = ref(false);
    
    // Panel resizing state
    const projectSidebarWidth = ref(200); // Default width for project sidebar
    const middlePanelWidth = ref(window.innerWidth * 0.25); // Adjust default width
    const MIN_PANEL_WIDTH = 250; // Minimum width in pixels
    let MAX_PANEL_WIDTH = window.innerWidth * 0.6; // Maximum width (60% of window)
    
    const newTask = ref({
      title: '',
      description: '',
      project_id: 1,
      priority: 'Must be done',
      status: 'To Do',
      due_date: new Date().toISOString().split('T')[0],
      assignee_id: ''
    });
    
    const newProject = ref({
      title: '',
      description: ''
    });
    
    // Current user (would come from auth)
    const user = ref({
      id: 1,
      name: 'Richard',
      avatar: null
    });
    
    // Constants
    const priorities = [
      { value: 'Must be done', label: 'Must Do' },
      { value: 'Important', label: 'Important' },
      { value: 'Good to have', label: 'Nice to Have' }
    ];

    const statuses = [
      { value: 'all', label: 'All' },
      { value: 'To Do', label: 'To Do' },
      { value: 'In Progress', label: 'In Progress' },
      { value: 'Completed', label: 'Done' }
    ];
    
    // Computed
    const filteredTasks = computed(() => {
      return tasks.value.filter(task => {
        // Filter by priority
        if (task.priority !== selectedPriority.value) {
          return false;
        }
        
        // Filter by status
        if (selectedStatus.value !== 'all' && task.status !== selectedStatus.value) {
          return false;
        }
        
        return true;
      });
    });
    
    // New computed property to filter by project
    const filteredTasksByProject = computed(() => {
      return filteredTasks.value.filter(task => {
        // Filter by project if one is selected
        if (selectedProjectId.value && task.project_id !== selectedProjectId.value) {
          return false;
        }
        
        return true;
      });
    });
    
    // Methods
    const checkAuth = async () => {
      if (!authService.isLoggedIn()) {
        window.location.href = '/login';
        return;
      }
      
      try {
        // Set the token in the Authorization header
        const token = authService.getToken();
        apiClient.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        
        // Try to get the current user to verify token is valid
        await apiClient.get('/user');
        
        // Load user info
        const userResponse = await apiClient.get('/user');
        user.value = userResponse.data;
      } catch (error) {
        console.error('Authentication check failed:', error);
        // Clear token and redirect to login
        authService.removeToken();
        window.location.href = '/login';
      }
    };
    
    const handleTaskPanelResize = (deltaX) => {
      const newWidth = middlePanelWidth.value + deltaX;
      
      // Enforce minimum and maximum widths
      if (newWidth >= MIN_PANEL_WIDTH && newWidth <= MAX_PANEL_WIDTH) {
        middlePanelWidth.value = newWidth;
        
        // Store the preference in localStorage for persistence
        localStorage.setItem('middlePanelWidth', newWidth);
      }
    };
    
    const handleProjectSidebarResize = (deltaX) => {
      const newWidth = projectSidebarWidth.value + deltaX;
      
      // Enforce minimum and maximum widths
      if (newWidth >= 180 && newWidth <= 300) {
        projectSidebarWidth.value = newWidth;
        
        // Store the preference in localStorage for persistence
        localStorage.setItem('projectSidebarWidth', newWidth);
      }
    };
    
    const toggleSidebar = () => {
      isSidebarCollapsed.value = !isSidebarCollapsed.value;
      localStorage.setItem('sidebarCollapsed', isSidebarCollapsed.value);
    };
    
    const fetchTasks = async () => {
      try {
        const response = await taskService.getAll();
        tasks.value = response.data.data || response.data;
      } catch (error) {
        console.error('Error fetching tasks:', error);
      }
    };

    const fetchProjects = async () => {
      try {
        const response = await projectService.getAll();
        projects.value = response.data.data || response.data;
      } catch (error) {
        console.error('Error fetching projects:', error);
      }
    };

    const fetchUsers = async () => {
      try {
        const response = await userService.getAll();
        users.value = response.data.data || response.data;
      } catch (error) {
        console.error('Error fetching users:', error);
      }
    };
    
    const updatePriority = (priority) => {
      selectedPriority.value = priority;
    };
    
    const updateStatus = (status) => {
      selectedStatus.value = status;
    };
    
    const selectProject = (project) => {
      if (selectedProjectId.value === project.id) {
        // Clicking the already selected project deselects it
        selectedProjectId.value = null;
      } else {
        selectedProjectId.value = project.id;
        // Pre-set the project_id in the new task form
        newTask.value.project_id = project.id;
      }
    };
    
    const selectTask = async (task) => {
      selectedTask.value = task;
      await fetchComments(task.id);
    };
    
    const fetchComments = async (taskId) => {
      try {
        const response = await commentService.getForTask(taskId);
        taskComments.value = response.data.data || response.data;
      } catch (error) {
        console.error('Error fetching comments:', error);
        taskComments.value = [];
      }
    };
    
    const addNewComment = async (content) => {
      try {
        const response = await commentService.create(selectedTask.value.id, content);
        await fetchComments(selectedTask.value.id);
      } catch (error) {
        console.error('Error adding comment:', error);
      }
    };
    
    const markAsCompleted = async (task) => {
      try {
        const taskCopy = { ...task, status: 'Completed' };
        const response = await taskService.update(task.id, taskCopy);
        
        // Update task in the list
        const index = tasks.value.findIndex(t => t.id === task.id);
        if (index !== -1) {
          tasks.value[index] = taskCopy;
        }
        
        // Update selected task if it's the same
        if (selectedTask.value && selectedTask.value.id === task.id) {
          selectedTask.value = taskCopy;
        }
      } catch (error) {
        console.error('Error marking task as completed:', error);
      }
    };
    
    const addTask = async () => {
      try {
        const response = await taskService.create(newTask.value);
        
        // Add the created task to the list
        tasks.value.push(response.data);
        
        // Reset form and close modal
        newTask.value = {
          title: '',
          description: '',
          project_id: selectedProjectId.value || projects.value[0]?.id || 1,
          priority: 'Must be done',
          status: 'To Do',
          due_date: new Date().toISOString().split('T')[0],
          assignee_id: ''
        };
        
        showAddTaskForm.value = false;
      } catch (error) {
        console.error('Error adding task:', error);
      }
    };
    
    const addProject = async () => {
      try {
        const response = await projectService.create(newProject.value);
        
        // Add the created project to the list
        projects.value.push(response.data);
        
        // Select the newly created project
        selectedProjectId.value = response.data.id;
        
        // Reset form and close modal
        newProject.value = {
          title: '',
          description: ''
        };
        
        showAddProjectForm.value = false;
      } catch (error) {
        console.error('Error adding project:', error);
      }
    };
    
    // Check screen size for responsive design
    const checkScreenSize = () => {
      isMobile.value = window.innerWidth < 768;
      
      // Adjust max panel width when window resizes
      MAX_PANEL_WIDTH = window.innerWidth * 0.6;
      
      // Ensure panel widths are within bounds after resize
      if (!isMobile.value) {
        if (middlePanelWidth.value > MAX_PANEL_WIDTH) {
          middlePanelWidth.value = MAX_PANEL_WIDTH;
        }
        
        if (projectSidebarWidth.value > 300) {
          projectSidebarWidth.value = 300;
        }
      }
    };
    
    // Lifecycle hooks
    onMounted(() => {
      // Check authentication before anything else
      checkAuth().then(() => {
        // Only fetch data if authenticated
        fetchTasks();
        fetchProjects();
        fetchUsers();
        
        // Check screen size initially and on resize
        checkScreenSize();
        window.addEventListener('resize', checkScreenSize);
        
        // Load saved panel width preference from localStorage
        const savedMiddlePanelWidth = localStorage.getItem('middlePanelWidth');
        if (savedMiddlePanelWidth && !isMobile.value) {
          middlePanelWidth.value = Math.min(Math.max(parseInt(savedMiddlePanelWidth), MIN_PANEL_WIDTH), MAX_PANEL_WIDTH);
        }
        
        // Load saved sidebar width and collapsed state
        const savedSidebarWidth = localStorage.getItem('projectSidebarWidth');
        if (savedSidebarWidth && !isMobile.value) {
          projectSidebarWidth.value = Math.min(Math.max(parseInt(savedSidebarWidth), 180), 300);
        }
        
        const savedSidebarState = localStorage.getItem('sidebarCollapsed');
        if (savedSidebarState !== null) {
          isSidebarCollapsed.value = savedSidebarState === 'true';
        }
      });
    });
    
    onBeforeUnmount(() => {
      // Clean up event listeners
      window.removeEventListener('resize', checkScreenSize);
    });
    
    return {
      tasks,
      projects,
      users,
      selectedTask,
      selectedPriority,
      selectedStatus,
      selectedProjectId,
      taskComments,
      showAddTaskForm,
      showAddProjectForm,
      newTask,
      newProject,
      user,
      priorities,
      statuses,
      filteredTasks,
      filteredTasksByProject,
      isMobile,
      isSidebarCollapsed,
      middlePanelWidth,
      projectSidebarWidth,
      selectTask,
      selectProject,
      updatePriority,
      updateStatus,
      addNewComment,
      markAsCompleted,
      addTask,
      addProject,
      handleTaskPanelResize,
      handleProjectSidebarResize,
      toggleSidebar,
      checkAuth
    };
  }
}
</script>

<style>
/* Base styles */
body {
  margin: 0;
  padding: 0;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
  background-color: #1a1a1a;
  color: #ffffff;
}

.zen-app {
  height: 100vh;
  width: 100%;
  overflow: hidden;
}

.zen-container {
  display: flex;
  height: 100%;
}

/* Project Sidebar */
.project-sidebar {
  width: 200px;
  min-width: 50px;
  max-width: 300px;
  border-right: 1px solid #333;
  background-color: #1a1a1a;
  position: relative;
  transition: width 0.2s ease;
  display: flex;
  flex-direction: column;
}

.project-sidebar.collapsed {
  width: 50px;
  min-width: 50px;
}

.sidebar-toggle {
  position: absolute;
  top: 10px;
  right: -12px;
  width: 24px;
  height: 24px;
  background-color: #333;
  border: none;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: #fff;
  z-index: 10;
  transition: background-color 0.2s ease;
}

.sidebar-toggle:hover {
  background-color: #444;
}

.sidebar-content {
  padding: 1rem;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.sidebar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.sidebar-header h3 {
  font-size: 0.9rem;
  font-weight: 500;
  color: #fff;
  letter-spacing: 0.5px;
}

.add-btn {
  width: 20px;
  height: 20px;
  background-color: #333;
  border: none;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: #fff;
}

.projects-list {
  margin-bottom: 1.5rem;
  overflow-y: auto;
}

.project-item {
  padding: 0.6rem 0.8rem;
  margin-bottom: 0.25rem;
  border-radius: 6px;
  background-color: #252525;
  font-size: 0.8rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.project-item:hover {
  background-color: #2a2a2a;
}

.project-item.active {
  background-color: rgba(138, 86, 255, 0.15);
  color: #fff;
}

.empty-projects,
.empty-notifications {
  text-align: center;
  padding: 1rem;
  color: #666;
  font-size: 0.7rem;
}

.sidebar-section {
  margin-top: auto;
  padding-top: 1rem;
  border-top: 1px solid #333;
}

.sidebar-section h4 {
  font-size: 0.8rem;
  font-weight: 500;
  color: #aaa;
  margin-bottom: 0.5rem;
}

/* Task Panel (now middle panel) */
.middle-panel {
  min-width: 250px;
  border-right: 1px solid #333;
  transition: width 0.1s ease;
}

.right-panel {
  flex: 1;
  overflow: hidden;
}

/* Modal */
.modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
}

.modal-content {
  background-color: #252525;
  width: 90%;
  max-width: 500px;
  border-radius: 8px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  border-bottom: 1px solid #333;
}

.modal-header h2 {
  font-size: 1rem;
  font-weight: 500;
  letter-spacing: 0.5px;
}

.close-modal-btn {
  background: none;
  border: none;
  color: #fff;
  font-size: 1.25rem;
  cursor: pointer;
  line-height: 1;
}

.modal-body {
  padding: 1rem;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.25rem;
  color: #ccc;
  font-size: 0.8rem;
}

.form-group input,
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 0.5rem;
  background-color: #2a2a2a;
  border: 1px solid #444;
  color: #fff;
  border-radius: 4px;
  font-family: inherit;
  font-size: 0.8rem;
}

.form-group textarea {
  min-height: 80px;
  resize: vertical;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  margin-top: 1.5rem;
}

.cancel-btn {
  background-color: transparent;
  border: 1px solid #444;
  color: #ccc;
  padding: 0.4rem 0.75rem;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.8rem;
}

.submit-btn {
  background-color: #8a56ff;
  color: #fff;
  border: none;
  padding: 0.4rem 0.75rem;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.8rem;
}

/* Responsive */
@media (max-width: 768px) {
  .zen-container {
    flex-direction: column;
  }
  
  .project-sidebar {
    display: none;
  }
  
  .middle-panel {
    width: 100% !important; /* Override inline style */
    height: 100%;
  }
  
  .zen-container.detail-open .middle-panel {
    display: none;
  }
  
  .zen-container.detail-open .right-panel {
    height: 100%;
  }
  
  .middle-panel.hidden {
    display: none;
  }
  
  .right-panel {
    height: 0;
    flex: none;
  }
  
  .zen-container.detail-open .right-panel {
    height: 100%;
  }
}
</style>