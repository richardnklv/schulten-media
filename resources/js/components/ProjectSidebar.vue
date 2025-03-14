<!-- resources/js/components/ProjectSidebar.vue -->
<template>
    <div class="project-sidebar" :class="{ 'collapsed': isCollapsed }">
      <!-- Toggle button -->
      <button class="sidebar-toggle" @click="toggleSidebar">
        <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none">
          <path d="M16 18l-6-6 6-6" v-if="!isCollapsed"></path>
          <path d="M9 18l6-6-6-6" v-else></path>
        </svg>
      </button>
      
      <!-- Sidebar content -->
      <div class="sidebar-content" v-if="!isCollapsed">
        <div class="sidebar-header">
          <h3>Projects</h3>
          <button class="add-btn" @click="$emit('add-project')">
            <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2" fill="none">
              <line x1="12" y1="5" x2="12" y2="19"></line>
              <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
          </button>
        </div>
        
        <div class="projects-list">
          <div 
            v-for="project in projects" 
            :key="project.id" 
            :class="['project-item', { 'active': selectedProjectId === project.id }]"
            @click="selectProject(project)"
          >
            {{ project.title }}
          </div>
          
          <div v-if="projects.length === 0" class="empty-projects">
            No projects available
          </div>
        </div>
        
        <div class="sidebar-section">
          <h4>Notifications</h4>
          <div class="empty-notifications">
            No new notifications
          </div>
        </div>
      </div>
    </div>
  </template>