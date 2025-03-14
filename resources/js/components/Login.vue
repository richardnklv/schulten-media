<template>
    <div class="login-container">
      <div class="login-card">
        <div class="brand">
          <h1>PROJECT MANAGEMENT</h1>
          <p class="tagline">Focus on what matters most</p>
        </div>
        
        <form @submit.prevent="handleLogin">
          <div class="alert error" v-if="error">
            {{ error }}
          </div>
          
          <div class="form-group">
            <label for="email">Email</label>
            <input 
              id="email" 
              type="email" 
              v-model="form.email" 
              required
              autofocus
              placeholder="your@email.com"
            >
          </div>
          
          <div class="form-group">
            <label for="password">Password</label>
            <input 
              id="password" 
              type="password" 
              v-model="form.password" 
              required
              placeholder="••••••••"
            >
          </div>
          
          <div class="form-actions">
            <button 
              type="submit" 
              class="btn btn-primary" 
              :disabled="loading"
            >
              <span v-if="loading">Logging in...</span>
              <span v-else>Login</span>
            </button>
          </div>
        </form>
        
        <div class="register-link">
          Don't have an account? 
          <a href="#" @click.prevent="showRegister = true">Register</a>
        </div>
      </div>
      
      <!-- Registration Modal -->
      <div class="modal" v-if="showRegister">
        <div class="modal-content">
          <div class="modal-header">
            <h2>Create Account</h2>
            <button class="close-modal-btn" @click="showRegister = false">&times;</button>
          </div>
          
          <div class="modal-body">
            <div class="alert error" v-if="registerError">
              {{ registerError }}
            </div>
            
            <form @submit.prevent="handleRegister">
              <div class="form-group">
                <label for="reg-name">Name</label>
                <input 
                  id="reg-name" 
                  type="text" 
                  v-model="registerForm.name" 
                  required
                  placeholder="Your full name"
                >
              </div>
              
              <div class="form-group">
                <label for="reg-email">Email</label>
                <input 
                  id="reg-email" 
                  type="email" 
                  v-model="registerForm.email" 
                  required
                  placeholder="your@email.com"
                >
              </div>
              
              <div class="form-group">
                <label for="reg-password">Password</label>
                <input 
                  id="reg-password" 
                  type="password" 
                  v-model="registerForm.password" 
                  required
                  placeholder="Min. 8 characters"
                >
              </div>
              
              <div class="form-group">
                <label for="reg-password-confirm">Confirm Password</label>
                <input 
                  id="reg-password-confirm" 
                  type="password" 
                  v-model="registerForm.password_confirmation" 
                  required
                  placeholder="Confirm password"
                >
              </div>
              
              <div class="form-actions">
                <button type="button" class="btn btn-secondary" @click="showRegister = false">
                  Cancel
                </button>
                <button type="submit" class="btn btn-primary" :disabled="registerLoading">
                  <span v-if="registerLoading">Creating account...</span>
                  <span v-else>Register</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import authService from '../services/authService';
  import apiClient from '../services/api';
  
  export default {
    name: 'Login',
    data() {
      return {
        form: {
          email: '',
          password: ''
        },
        loading: false,
        error: null,
        showRegister: false,
        registerForm: {
          name: '',
          email: '',
          password: '',
          password_confirmation: ''
        },
        registerLoading: false,
        registerError: null
      };
    },
    mounted() {
      // Check if user is already logged in
      if (authService.isLoggedIn()) {
        // Set the Authorization header
        const token = authService.getToken();
        apiClient.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        
        // Redirect to dashboard
        window.location.href = '/dashboard';
      }
    },
    methods: {
      async handleLogin() {
        this.loading = true;
        this.error = null;
        
        try {
          const response = await authService.login(this.form);
          
          // Store the auth token
          authService.setToken(response.data.token);
          
          // Set the Authorization header for subsequent API calls
          apiClient.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`;
          
          // Store user data if needed
          localStorage.setItem('user_data', JSON.stringify(response.data.user));
          
          // Redirect to dashboard
          window.location.href = '/dashboard';
        } catch (error) {
          if (error.response && error.response.data && error.response.data.message) {
            this.error = error.response.data.message;
          } else {
            this.error = 'An error occurred. Please try again.';
          }
          console.error('Login error:', error);
        } finally {
          this.loading = false;
        }
      },
      
      async handleRegister() {
        this.registerLoading = true;
        this.registerError = null;
        
        try {
          const response = await authService.register(this.registerForm);
          
          // Store the auth token
          authService.setToken(response.data.token);
          
          // Set the Authorization header for subsequent API calls
          apiClient.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`;
          
          // Store user data if needed
          localStorage.setItem('user_data', JSON.stringify(response.data.user));
          
          // Redirect to dashboard
          window.location.href = '/dashboard';
        } catch (error) {
          if (error.response && error.response.data && error.response.data.message) {
            this.registerError = error.response.data.message;
          } else if (error.response && error.response.data && error.response.data.errors) {
            // Process validation errors
            const errors = error.response.data.errors;
            const firstError = Object.values(errors)[0][0];
            this.registerError = firstError;
          } else {
            this.registerError = 'An error occurred. Please try again.';
          }
          console.error('Registration error:', error);
        } finally {
          this.registerLoading = false;
        }
      }
    }
  };
  </script>
  
  <style scoped>
  .login-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    background-color: #1a1a1a;
    background-image: linear-gradient(60deg, #1a1a1a 0%, #2d2d2d 100%);
    padding: 2rem;
  }
  
  .login-card {
    width: 100%;
    max-width: 420px;
    background-color: #252525;
    border-radius: 12px;
    box-shadow: 0 16px 32px rgba(0, 0, 0, 0.2);
    padding: 2.5rem;
    transition: transform 0.3s, box-shadow 0.3s;
  }
  
  .login-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
  }
  
  .brand {
    text-align: center;
    margin-bottom: 2.5rem;
  }
  
  .brand h1 {
    font-size: 1.6rem;
    font-weight: 600;
    letter-spacing: 1px;
    margin: 0;
    color: #fff;
  }
  
  .tagline {
    color: #8a56ff;
    font-size: 0.9rem;
    margin-top: 0.5rem;
    font-weight: 300;
    letter-spacing: 0.5px;
  }
  
  .form-group {
    margin-bottom: 1.5rem;
  }
  
  .form-group label {
    display: block;
    margin-bottom: 0.5rem;
    color: #ccc;
    font-size: 0.9rem;
    letter-spacing: 0.5px;
  }
  
  .form-group input {
    width: 100%;
    padding: 0.75rem 1rem;
    background-color: #333;
    border: 1px solid #444;
    border-radius: 8px;
    color: #fff;
    font-size: 1rem;
    transition: border-color 0.2s, box-shadow 0.2s;
  }
  
  .form-group input:focus {
    border-color: #8a56ff;
    box-shadow: 0 0 0 2px rgba(138, 86, 255, 0.2);
    outline: none;
  }
  
  .form-group input::placeholder {
    color: #777;
  }
  
  .form-actions {
    margin-top: 2rem;
  }
  
  .btn {
    display: block;
    width: 100%;
    padding: 0.75rem;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.2s, transform 0.1s;
    letter-spacing: 0.5px;
  }
  
  .btn:active {
    transform: scale(0.98);
  }
  
  .btn-primary {
    background-color: #8a56ff;
    color: #fff;
  }
  
  .btn-primary:hover {
    background-color: #7445e0;
  }
  
  .btn-primary:disabled {
    background-color: #6f5ba7;
    cursor: not-allowed;
  }
  
  .btn-secondary {
    background-color: transparent;
    color: #fff;
    border: 1px solid #444;
  }
  
  .btn-secondary:hover {
    background-color: #333;
  }
  
  .register-link {
    text-align: center;
    margin-top: 1.5rem;
    color: #ccc;
    font-size: 0.9rem;
  }
  
  .register-link a {
    color: #8a56ff;
    text-decoration: none;
    transition: color 0.2s;
  }
  
  .register-link a:hover {
    color: #7445e0;
    text-decoration: underline;
  }
  
  .alert {
    padding: 0.75rem 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    font-size: 0.9rem;
  }
  
  .alert.error {
    background-color: rgba(231, 76, 60, 0.1);
    border: 1px solid rgba(231, 76, 60, 0.2);
    color: #e74c3c;
  }
  
  .modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 100;
    animation: fadeIn 0.2s;
  }
  
  .modal-content {
    background-color: #252525;
    width: 90%;
    max-width: 500px;
    border-radius: 12px;
    box-shadow: 0 16px 32px rgba(0, 0, 0, 0.3);
    animation: slideUp 0.3s;
  }
  
  .modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #333;
  }
  
  .modal-header h2 {
    font-size: 1.25rem;
    font-weight: 500;
    color: #fff;
    margin: 0;
  }
  
  .close-modal-btn {
    background: none;
    border: none;
    color: #fff;
    font-size: 1.5rem;
    cursor: pointer;
    line-height: 1;
  }
  
  .modal-body {
    padding: 1.5rem;
  }
  
  .form-actions {
    display: flex;
    gap: 1rem;
  }
  
  @keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
  }
  
  @keyframes slideUp {
    from { transform: translateY(30px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
  }
  
  @media (max-width: 480px) {
    .login-card {
      padding: 2rem 1.5rem;
    }
  }
  </style>