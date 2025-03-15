import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './components/App.vue';
import router from './router';
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

// Create Pinia instance
const pinia = createPinia();

// Create Vue app with router and Pinia
const app = createApp(App);
app.use(router);
app.use(pinia);
app.mount('#app');