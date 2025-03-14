import apiClient from './api';

export default {
  login(credentials) {
    return apiClient.post('/login', credentials);
  },
  
  register(userData) {
    return apiClient.post('/register', userData);
  },
  
  logout() {
    return apiClient.post('/logout');
  },
  
  getCurrentUser() {
    return apiClient.get('/user');
  },
  
  setToken(token) {
    localStorage.setItem('auth_token', token);
  },
  
  getToken() {
    return localStorage.getItem('auth_token');
  },
  
  removeToken() {
    localStorage.removeItem('auth_token');
  },
  
  isLoggedIn() {
    return !!this.getToken();
  }
};