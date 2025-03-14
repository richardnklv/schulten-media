import apiClient from './api';

export default {
  getForTask(taskId) {
    return apiClient.get(`/tasks/${taskId}/comments`);
  },
  
  create(taskId, content) {
    return apiClient.post(`/tasks/${taskId}/comments`, { content });
  },
  
  update(id, content) {
    return apiClient.put(`/comments/${id}`, { content });
  },
  
  delete(id) {
    return apiClient.delete(`/comments/${id}`);
  }
};