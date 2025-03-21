# Project Management System

A full-stack project management application built with Laravel backend, Filament admin panel, and Vue.js frontend.

## Project Structure

The application follows a modern full-stack architecture:

### Backend (Laravel)
- **Controllers**: API controllers for projects, tasks, users, notifications, and comments
- **Models**: Eloquent models for all database entities
- **Resources**: Filament admin panel resource definitions
- **Services**: Business logic separated into service classes

### Frontend (Vue.js)
- **Components**: Organized by functionality
  - `detail-panel`: Task and project details views
  - `sidebar`: Navigation and project selection
  - `task-panel`: Task management components
- **Services**: API communication services for each entity
- **Stores**: Pinia state management
- **Router**: Vue Router configuration

### Admin Panel
- Filament-powered admin dashboard with resource management
- Custom admin panel provider and configurations

## Tech Stack

- **Backend**: Laravel 10, REST API, SQLite
- **Admin**: Filament PHP
- **Frontend**: Vue.js 3, Pinia store
- **Authentication**: Laravel Sanctum

## Features

- **Task Management**: Create, assign, prioritize, and track tasks
- **Project Organization**: Group tasks by projects with detailed overviews
- **Real-time Notifications**: Get instant updates on task changes
- **User Management**: User roles and permissions
- **File Attachments**: Upload and manage documents for tasks

## Getting Started

```bash
# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate
php artisan migrate --seed

# Run application
php artisan serve
npm run dev
```

## Access
- **Main Application**: http://127.0.0.1:8000/dashboard
- **Admin Panel**: http://127.0.0.1:8000/admin

