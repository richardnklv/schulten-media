import { defineStore } from 'pinia';
import taskService from '../services/taskService';

export const taskStore = defineStore('tasks', {
  state: () => ({
    tasks: [],
    loading: false,
    error: null,
    // Track drag operation
    isDragging: false,
    draggedTaskId: null
  }),
  
  getters: {
    // Filter tasks by priority
    getTasksByPriority: (state) => (priority) => {
      if (priority === 'all') return state.tasks;
      return state.tasks.filter(task => task.priority === priority);
    },
    
    // Filter tasks by status
    getTasksByStatus: (state) => (status) => {
      if (status === 'all') return state.tasks;
      return state.tasks.filter(task => task.status === status);
    },
    
    // Filter tasks by both priority and status
    getFilteredTasks: (state) => (priority, status) => {
      let filtered = state.tasks;
      
      if (priority !== 'all') {
        filtered = filtered.filter(task => task.priority === priority);
      }
      
      if (status !== 'all') {
        filtered = filtered.filter(task => task.status === status);
      }
      
      return filtered;
    },
    
    // Search tasks by query
    searchTasks: (state) => (query, tasks = state.tasks) => {
      if (!query.trim()) return tasks;
      
      // Process query to be more flexible in search
      const processedQuery = query.toLowerCase().trim();
      const noSpacesQuery = processedQuery.replace(/\s+/g, '');
      
      return tasks.filter(task => {
        const titleLower = task.title.toLowerCase();
        const descLower = task.description ? task.description.toLowerCase() : '';
        const titleNoSpaces = titleLower.replace(/\s+/g, '');
        const descNoSpaces = descLower.replace(/\s+/g, '');
        
        return titleLower.includes(processedQuery) || 
               descLower.includes(processedQuery) ||
               titleNoSpaces.includes(noSpacesQuery) ||
               descNoSpaces.includes(noSpacesQuery);
      });
    },
    
    // Get tasks grouped by priority (for drag and drop UI)
    getTasksGroupedByPriority: (state) => (priorityOptions, filteredTasks = state.tasks) => {
      const grouped = {};
      
      // Initialize empty arrays for each priority
      priorityOptions.forEach(priority => {
        if (priority.value !== 'all') {
          grouped[priority.value] = [];
        }
      });
      
      // Populate with tasks
      filteredTasks.forEach(task => {
        if (task.priority && task.priority !== 'all') {
          if (!grouped[task.priority]) {
            grouped[task.priority] = [];
          }
          grouped[task.priority].push(task);
        }
      });
      
      return grouped;
    }
  },
  
  actions: {
    // Fetch all tasks
    async fetchTasks() {
      this.loading = true;
      this.error = null;
      try {
        const response = await taskService.getAll();
        this.tasks = response.data;
      } catch (error) {
        this.error = error.message || 'Failed to fetch tasks';
        console.error('Error fetching tasks:', error);
      } finally {
        this.loading = false;
      }
    },
    
    // Create a new task
    async createTask(task) {
      this.loading = true;
      this.error = null;
      try {
        const response = await taskService.create(task);
        this.tasks.push(response.data);
        return response.data;
      } catch (error) {
        this.error = error.message || 'Failed to create task';
        console.error('Error creating task:', error);
        throw error;
      } finally {
        this.loading = false;
      }
    },
    
    // Update a task
    async updateTask(id, updates) {
      this.loading = true;
      this.error = null;
      try {
        const response = await taskService.update(id, updates);
        const index = this.tasks.findIndex(task => task.id === id);
        if (index !== -1) {
          this.tasks[index] = response.data;
        }
        return response.data;
      } catch (error) {
        this.error = error.message || 'Failed to update task';
        console.error('Error updating task:', error);
        throw error;
      } finally {
        this.loading = false;
      }
    },
    
    // Delete a task
    async deleteTask(id) {
      this.loading = true;
      this.error = null;
      try {
        await taskService.delete(id);
        this.tasks = this.tasks.filter(task => task.id !== id);
      } catch (error) {
        this.error = error.message || 'Failed to delete task';
        console.error('Error deleting task:', error);
        throw error;
      } finally {
        this.loading = false;
      }
    },
    
    // Update task priority (used with drag and drop)
    async updateTaskPriority(taskId, newPriority) {
      // First, optimistically update the local state for immediate UI feedback
      const taskIndex = this.tasks.findIndex(task => task.id === taskId);
      if (taskIndex === -1) return;
      
      const originalPriority = this.tasks[taskIndex].priority;
      
      // Update locally first (optimistic update)
      this.tasks[taskIndex].priority = newPriority;
      
      try {
        // Then update on the server
        await taskService.updatePriority(taskId, newPriority);
      } catch (error) {
        // Revert the change if the server update fails
        this.tasks[taskIndex].priority = originalPriority;
        this.error = error.message || 'Failed to update task priority';
        console.error('Error updating task priority:', error);
        throw error;
      }
    },
    
    // Start drag operation
    startDragging(taskId) {
      this.isDragging = true;
      this.draggedTaskId = taskId;
    },
    
    // End drag operation
    endDragging() {
      this.isDragging = false;
      this.draggedTaskId = null;
    },
    
    // Add attachments to a task
    async addAttachments(taskId, files) {
      this.loading = true;
      this.error = null;
      try {
        const response = await taskService.addAttachments(taskId, files);
        
        // Update the local task with new attachments
        const taskIndex = this.tasks.findIndex(task => task.id === taskId);
        if (taskIndex !== -1 && response.data.attachments) {
          this.tasks[taskIndex].attachments = response.data.attachments;
        }
        
        return response.data;
      } catch (error) {
        this.error = error.message || 'Failed to add attachments';
        console.error('Error adding attachments:', error);
        throw error;
      } finally {
        this.loading = false;
      }
    }
  }
});
