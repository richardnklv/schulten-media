<template>
  <div class="zen-app">
    <div class="zen-container" :class="{ 'detail-open': selectedTask && isMobile }">
      <!-- Left Panel - Project Sidebar -->
      <div class="left-panel" :class="{ 'hidden': selectedTask && isMobile, 'collapsed': sidebarCollapsed }" :style="{ width: sidebarCollapsed ? '60px' : leftPanelWidth + 'px' }">
        <ProjectSidebar 
          :user="user"
          @toggle-sidebar="toggleSidebar"
          @select-project="filterTasksByProject"
        />
      </div>
      
      <!-- Resizable Splitter for Left/Middle -->
      <ResizableSplitter 
        v-if="!isMobile && !sidebarCollapsed" 
        @resize="handleResize"
        class="left-splitter"
      />
      
      <!-- Middle Panel - Task Panel -->
      <div class="middle-panel" :class="{ 'hidden': selectedTask && isMobile }" :style="{ width: middlePanelWidth + 'px' }">
        <TaskPanel 
          :user-name="user.name"
          :tasks="filteredTasks"
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
      
      <!-- Resizable Splitter for Middle/Right -->
      <ResizableSplitter 
        v-if="!isMobile" 
        @resize="handleRightResize"
        class="right-splitter"
      />
      
      <!-- Right Panel - Task Details & Progress -->
      <div class="right-panel">
        <DetailPanel 
          :selected-task="selectedTask"
          :comments="taskComments"
          :is-mobile="isMobile"
          @close-task="selectedTask = null"
          @complete-task="markAsCompleted"
          @update-task="updateTask"
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
  </div>
</template>

<script>
import authService from '../services/authService.js';
import apiClient from '../services/api.js';
import ProjectSidebar from './sidebar/ProjectSidebar.vue';
import TaskPanel from './TaskPanel.vue';
import DetailPanel from './DetailPanel.vue';
import ResizableSplitter from './ResizableSplitter.vue';
import taskService from '../services/taskService.js';
import { useTaskStore } from '../stores/taskStore';
import projectService from '../services/projectService.js';
import userService from '../services/userService.js';
import commentService from '../services/commentService.js';
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';

export default {
  name: 'DashboardContainer',
  components: {
    ProjectSidebar,
    TaskPanel,
    DetailPanel,
    ResizableSplitter
  },
  setup() {
    // Initialize the task store
    const taskStore = useTaskStore();
    
    // State
    const tasks = ref([]);
    const projects = ref([]);
    const users = ref([]);
    const sidebarCollapsed = ref(false);
    
    const selectedTask = ref(null);
    const selectedPriority = ref('all'); // Changed default to 'all'
    const selectedStatus = ref('all');
    const selectedProject = ref(null);
    const taskComments = ref([]);
    const showAddTaskForm = ref(false);
    const isMobile = ref(false);
    
    // Panel resizing state
    const MIN_PANEL_WIDTH = 200; // Minimum width in pixels
    let MAX_PANEL_WIDTH = window.innerWidth * 0.6; // Maximum width (60% of window)
    const leftPanelWidth = ref(Math.max(MIN_PANEL_WIDTH, Math.min(window.innerWidth * 0.15, MAX_PANEL_WIDTH))); // Default to 15% of window width (reduced from 20%)
    const middlePanelWidth = ref(Math.max(MIN_PANEL_WIDTH, Math.min(window.innerWidth * 0.40, MAX_PANEL_WIDTH))); // Default to 40% of window width
    
    const newTask = ref({
      title: '',
      description: '',
      project_id: 1,
      priority: 'Must be done',
      status: 'To Do',
      due_date: new Date().toISOString().split('T')[0],
      assignee_id: ''
    });
    
    // Current user (would come from auth)
    const user = ref({
      id: 1,
      name: 'Richard',
      avatar: null
    });
    
    // Constants
    const priorities = [
      { value: 'all', label: 'All' },
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
      // First filter tasks
      const filtered = tasks.value.filter(task => {
        // Always filter by project if a project is selected
        if (selectedProject.value && task.project_id !== selectedProject.value) {
          return false;
        }
        
        // Filter by priority (skip if 'all' is selected)
        if (selectedPriority.value !== 'all' && task.priority !== selectedPriority.value) {
          return false;
        }
        
        // Filter by status
        if (selectedStatus.value !== 'all' && task.status !== selectedStatus.value) {
          return false;
        }
        
        return true;
      });
      
      // Define priority order for sorting
      const priorityOrder = {
        'Must be done': 1,
        'Important': 2,
        'Good to have': 3
      };
      
      // Sort by priority and then by due date within each priority
      return filtered.sort((a, b) => {
        // First sort by priority
        const priorityA = priorityOrder[a.priority] || 999;
        const priorityB = priorityOrder[b.priority] || 999;
        
        if (priorityA !== priorityB) {
          return priorityA - priorityB;
        }
        
        // Then sort by due date
        return new Date(a.due_date) - new Date(b.due_date);
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

    const handleResize = (deltaX) => {
      const newWidth = leftPanelWidth.value + deltaX;
      
      // Enforce minimum and maximum widths
      if (newWidth >= MIN_PANEL_WIDTH && newWidth <= MAX_PANEL_WIDTH) {
        leftPanelWidth.value = newWidth;
        
        // Store the preference in localStorage for persistence
        localStorage.setItem('leftPanelWidth', newWidth);
      }
    };
    
    const handleRightResize = (deltaX) => {
      const newWidth = middlePanelWidth.value + deltaX;
      
      // Enforce minimum and maximum widths
      if (newWidth >= MIN_PANEL_WIDTH && newWidth <= MAX_PANEL_WIDTH) {
        middlePanelWidth.value = newWidth;
        
        // Store the preference in localStorage for persistence
        localStorage.setItem('middlePanelWidth', newWidth);
      }
    };
    
    const fetchTasks = async () => {
      try {
        await taskStore.fetchTasks();
        tasks.value = taskStore.tasks;
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
    
    const updateTask = async (updatedTask) => {
      try {
        // Task has already been updated in the API by the TaskDetails component
        
        // Update task in the list
        const index = tasks.value.findIndex(t => t.id === updatedTask.id);
        if (index !== -1) {
          tasks.value[index] = updatedTask;
        }
        
        // Update selected task if it's the same
        if (selectedTask.value && selectedTask.value.id === updatedTask.id) {
          selectedTask.value = updatedTask;
        }
      } catch (error) {
        console.error('Error updating task:', error);
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
          project_id: 1,
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
    
    // Check screen size for responsive design
    const checkScreenSize = () => {
      isMobile.value = window.innerWidth < 768;
      
      // Adjust max panel width when window resizes
      MAX_PANEL_WIDTH = window.innerWidth * 0.6;
      
      // Ensure panel widths are within bounds after resize
      if (!isMobile.value) {
        // Check left panel width
        if (leftPanelWidth.value > MAX_PANEL_WIDTH) {
          leftPanelWidth.value = MAX_PANEL_WIDTH;
        }

        // Check middle panel width
        if (middlePanelWidth.value > MAX_PANEL_WIDTH) {
          middlePanelWidth.value = MAX_PANEL_WIDTH;
        }
        
        // Ensure minimum widths
        if (leftPanelWidth.value < MIN_PANEL_WIDTH) {
          leftPanelWidth.value = MIN_PANEL_WIDTH;
        }
        
        if (middlePanelWidth.value < MIN_PANEL_WIDTH) {
          middlePanelWidth.value = MIN_PANEL_WIDTH;
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
        const savedWidth = localStorage.getItem('leftPanelWidth');
        if (savedWidth && !isMobile.value) {
          leftPanelWidth.value = Math.min(Math.max(parseInt(savedWidth), MIN_PANEL_WIDTH), MAX_PANEL_WIDTH);
        }
        
        const savedMiddleWidth = localStorage.getItem('middlePanelWidth');
        if (savedMiddleWidth && !isMobile.value) {
          middlePanelWidth.value = Math.min(Math.max(parseInt(savedMiddleWidth), MIN_PANEL_WIDTH), MAX_PANEL_WIDTH);
        }
      });
    });
    
    onBeforeUnmount(() => {
      // Clean up event listeners
      window.removeEventListener('resize', checkScreenSize);
    });
    
    // Toggle sidebar method
    const toggleSidebar = (collapsed) => {
      sidebarCollapsed.value = collapsed;
    };
    
    // Filter tasks by project
    const filterTasksByProject = (projectId) => {
      selectedProject.value = projectId;
    };
    
    // Update tasks when taskStore changes 
    watch(() => taskStore.tasks, (newTasks) => {
      tasks.value = newTasks;
    });
    
    return {
      taskStore,
      tasks,
      projects,
      users,
      selectedTask,
      selectedPriority,
      selectedStatus,
      selectedProject,
      taskComments,
      showAddTaskForm,
      newTask,
      user,
      priorities,
      statuses,
      filteredTasks,
      isMobile,
      leftPanelWidth,
      middlePanelWidth,
      sidebarCollapsed,
      selectTask,
      updatePriority,
      updateStatus,
      addNewComment,
      markAsCompleted,
      updateTask,
      addTask,
      handleResize,
      handleRightResize,
      toggleSidebar,
      filterTasksByProject,
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

.left-panel {
  min-width: 60px;
  border-right: 1px solid #333;
  transition: all 0.3s ease;
}

.left-panel.collapsed {
  min-width: 60px;
  width: 60px !important;
}

.middle-panel {
  min-width: 300px;
  border-right: 1px solid #333;
  transition: width 0.1s ease;
}

.right-panel {
  flex: 1;
  overflow: hidden;
}

/* Splitter customization */
.left-splitter, .right-splitter {
  position: relative;
  z-index: 10;
}

.left-splitter .splitter-handle {
  background-color: #333;
}

.right-splitter .splitter-handle {
  background-color: #333;
}

.left-splitter:hover .splitter-handle {
  background-color: #8a56ff;
}

.right-splitter:hover .splitter-handle {
  background-color: #46a9ee;
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
  
  .left-panel, .middle-panel {
    width: 100% !important; /* Override inline style */
    height: 50%;
  }
  
  .zen-container.detail-open .left-panel,
  .zen-container.detail-open .middle-panel {
    display: none;
  }
  
  .zen-container.detail-open .right-panel {
    height: 100%;
  }
  
  .left-panel.hidden,
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