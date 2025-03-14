import apiClient from './api';

export default {
  getAll() {
    return apiClient.get('/users');
  },
  
  get(id) {
    return apiClient.get(`/users/${id}`);
  },
  
  update(id, userData) {
    return apiClient.put(`/users/${id}`, userData);
  },
  
  getCurrentUser() {
    return apiClient.get('/user');
  }
};