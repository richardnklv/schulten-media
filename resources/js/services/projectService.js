import apiClient from './api';

export default {
  getAll() {
    return apiClient.get('/projects');
  },
  
  get(id) {
    return apiClient.get(`/projects/${id}`);
  },
  
  create(project) {
    return apiClient.post('/projects', project);
  },
  
  update(id, project) {
    return apiClient.put(`/projects/${id}`, project);
  },
  
  delete(id) {
    return apiClient.delete(`/projects/${id}`);
  }
};