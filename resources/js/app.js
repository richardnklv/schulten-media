import { createApp } from 'vue';
import Dashboard from './components/Dashboard.vue';
import Login from './components/Login.vue';
import axios from 'axios';
import authService from './services/authService';

// Set up axios defaults
axios.defaults.baseURL = '/api';
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['Content-Type'] = 'application/json';
axios.defaults.headers.common['Accept'] = 'application/json';

// Get CSRF token
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found');
}

// Initialize auth from localStorage if token exists
if (authService.isLoggedIn()) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${authService.getToken()}`;
}

// Create Vue app
const app = createApp({});
app.component('dashboard', Dashboard);
app.component('login', Login);
app.mount('#app');