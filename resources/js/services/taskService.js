import apiClient from './api';

export default {
  getAll() {
    return apiClient.get('/tasks');
  },
  
  get(id) {
    return apiClient.get(`/tasks/${id}`);
  },
  
  create(task) {
    return apiClient.post('/tasks', task);
  },
  
  update(id, task) {
    return apiClient.put(`/tasks/${id}`, task);
  },
  
  delete(id) {
    return apiClient.delete(`/tasks/${id}`);
  },
  
  updatePriority(id, priority) {
    return apiClient.put(`/tasks/${id}/priority`, { priority });
  }
};