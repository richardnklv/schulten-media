import apiClient from './api';
import notificationService from './notificationService';
import axios from 'axios';

export default {
  getAll() {
    return apiClient.get('/tasks');
  },
  
  get(id) {
    return apiClient.get(`/tasks/${id}`);
  },
  
  async create(task) {
    // Check if task has file attachments
    if (task.attachments && task.attachments.length > 0) {
      const formData = new FormData();
      
      // Add task fields to FormData
      formData.append('project_id', task.project_id);
      formData.append('title', task.title);
      if (task.description) formData.append('description', task.description);
      formData.append('due_date', task.due_date);
      formData.append('priority', task.priority);
      formData.append('status', task.status);
      if (task.assignee_id) formData.append('assignee_id', task.assignee_id);
      
      // Add file attachments
      for (let i = 0; i < task.attachments.length; i++) {
        formData.append('attachments[]', task.attachments[i]);
      }
      
      // Use axios directly with modified headers for file upload
      const token = localStorage.getItem('auth_token');
      const response = await axios.post('/api/tasks', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
          'Authorization': token ? `Bearer ${token}` : '',
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      });
      
      // Add notification
      notificationService.addNotification({
        type: 'task_created',
        title: 'New Task Created',
        content: `Task "${task.title}" has been created with attachments`,
        time: new Date(),
        entityId: response.data.id,
        entityType: 'task'
      });
      
      return response;
    } else {
      // Regular task creation without attachments
      const response = await apiClient.post('/tasks', task);
      
      // Add notification
      notificationService.addNotification({
        type: 'task_created',
        title: 'New Task Created',
        content: `Task "${task.title}" has been created`,
        time: new Date(),
        entityId: response.data.id,
        entityType: 'task'
      });
      
      return response;
    }
  },
  
  async update(id, task) {
    const response = await apiClient.put(`/tasks/${id}`, task);
    
    // Add notification for task update
    notificationService.addNotification({
      type: 'task_updated',
      title: 'Task Updated',
      content: `Task "${task.title}" has been updated`,
      time: new Date(),
      entityId: id,
      entityType: 'task'
    });
    
    return response;
  },
  
  delete(id) {
    return apiClient.delete(`/tasks/${id}`);
  },
  
  async updatePriority(id, priority) {
    const response = await apiClient.put(`/tasks/${id}/priority`, { priority });
    
    // Add notification for priority update
    const task = response.data;
    notificationService.addNotification({
      type: 'task_priority_changed',
      title: 'Task Priority Changed',
      content: `Task "${task.title}" priority set to ${priority}`,
      time: new Date(),
      entityId: id,
      entityType: 'task'
    });
    
    return response;
  },
  
  // Method for adding file attachments to an existing task
  async addAttachments(taskId, files) {
    if (!files || files.length === 0) return;
    
    // First get the task to have all the data
    const taskResponse = await this.get(taskId);
    const task = taskResponse.data;
    
    const formData = new FormData();
    
    // Add file attachments
    for (let i = 0; i < files.length; i++) {
      formData.append('attachments[]', files[i]);
    }
    
    // Use the new dedicated endpoint for adding attachments to a task
    const response = await apiClient.post(`/tasks/${taskId}/attachments`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
    
    // Add notification
    notificationService.addNotification({
      type: 'attachments_added',
      title: 'Files Attached',
      content: `${files.length} file(s) attached to task "${task.title}"`,
      time: new Date(),
      entityId: taskId,
      entityType: 'task'
    });
    
    return response;
  }
};