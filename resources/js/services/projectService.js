import apiClient from './api';
import notificationService from './notificationService';

export default {
  getAll() {
    return apiClient.get('/projects');
  },
  
  get(id) {
    return apiClient.get(`/projects/${id}`);
  },
  
  async create(project) {
    const response = await apiClient.post('/projects', project);
    // Add notification for new project
    notificationService.addNotification({
      type: 'project_created',
      title: 'New Project Created',
      content: `Project "${project.title}" has been created`,
      time: new Date(),
      entityId: response.data.id,
      entityType: 'project'
    });
    return response;
  },
  
  async update(id, project) {
    const response = await apiClient.put(`/projects/${id}`, project);
    // Add notification for project update
    notificationService.addNotification({
      type: 'project_updated',
      title: 'Project Updated',
      content: `Project "${project.title}" has been updated`,
      time: new Date(),
      entityId: id,
      entityType: 'project'
    });
    return response;
  },
  
  delete(id) {
    return apiClient.delete(`/projects/${id}`);
  }
};