<template>
  <div class="overview-container" :class="{ 'expanded': isExpanded }">
    <div class="overview-header">
      <div>
        <h2>This Week</h2>
        <p class="overview-subtitle">{{ weekRange }}</p>
      </div>
      <button class="expand-btn" @click="toggleExpand" :title="isExpanded ? 'Collapse view' : 'Expand to calendar view'">
        <svg v-if="!isExpanded" viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="1.5" fill="none">
          <path d="M8 3v3a2 2 0 0 1-2 2H3m18 0h-3a2 2 0 0 1-2-2V3m0 18v-3a2 2 0 0 1 2-2h3M3 16h3a2 2 0 0 1 2 2v3"></path>
        </svg>
        <svg v-else viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="1.5" fill="none">
          <path d="M4 14h6m10 0h-6m0 0V4m0 10v6"></path>
        </svg>
      </button>
    </div>
    
    <!-- Compact view (vertical list by day) -->
    <div v-if="!isExpanded" class="week-list">
      <div 
        v-for="day in weekdaysWithTasks" 
        :key="day.date" 
        class="day-section"
        :class="{ 'current-day-section': day.isToday }"
      >
        <div class="day-header">
          <span class="day-name">{{ day.fullName }}</span>
          <span class="day-date">{{ day.formattedDate }}</span>
        </div>
        
        <div v-if="day.tasks.length === 0" class="no-tasks">
          <span>No tasks</span>
        </div>
        
        <div v-else class="task-list">
          <div 
            v-for="task in day.tasks" 
            :key="task.id" 
            class="task-row"
            :class="getTaskClasses(task)"
          >
            <span class="task-title">{{ task.title }}</span>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Expanded view (calendar grid) -->
    <div v-else class="week-grid">
      <!-- Day headers -->
      <div class="day-headers">
        <div 
          v-for="day in weekdays" 
          :key="day.date" 
          class="day-label"
          :class="{ 'current-day': day.isToday }"
        >
          <span class="day-name">{{ day.name }}</span>
          <span class="day-date">{{ day.shortDate }}</span>
        </div>
      </div>
      
      <!-- Tasks grid -->
      <div class="tasks-grid">
        <div 
          v-for="day in weekdays" 
          :key="day.date" 
          class="day-column"
          :class="{ 'current-day': day.isToday }"
        >
          <div v-if="day.tasks.length === 0" class="empty-day"></div>
          
          <div 
            v-for="task in day.tasks" 
            :key="task.id" 
            class="task-item"
            :class="getTaskClasses(task)"
          >
            <span class="task-title">{{ task.title }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import taskService from '../../services/taskService';

export default {
  name: 'OverviewView',
  data() {
    return {
      tasks: [],
      loading: false,
      error: null,
      weekdays: [],
      isExpanded: false
    }
  },
  mounted() {
    this.fetchTasks();
    this.generateWeekDays();
  },
  methods: {
    toggleExpand() {
      this.isExpanded = !this.isExpanded;
      // Emit event to parent component to handle layout changes
      this.$emit('toggle-expand', this.isExpanded);
    },
    
    async fetchTasks() {
      this.loading = true;
      try {
        const response = await taskService.getAll();
        this.tasks = response.data;
        this.assignTasksToDays();
      } catch (error) {
        this.error = 'Failed to load tasks';
        console.error('Error fetching tasks:', error);
      } finally {
        this.loading = false;
      }
    },
    
    generateWeekDays() {
      const today = new Date();
      const currentDay = today.getDay(); // 0 = Sunday, 1 = Monday, etc.
      
      // Find the date of the Monday of this week
      const mondayOffset = currentDay === 0 ? -6 : 1 - currentDay;
      const monday = new Date(today);
      monday.setDate(today.getDate() + mondayOffset);
      
      // Generate array of weekdays from Monday to Sunday
      this.weekdays = [];
      for (let i = 0; i < 7; i++) {
        const date = new Date(monday);
        date.setDate(monday.getDate() + i);
        
        this.weekdays.push({
          name: date.toLocaleDateString('en-US', { weekday: 'short' }),
          date: date.toISOString().split('T')[0], // YYYY-MM-DD format
          shortDate: date.getDate(), // Day of month (1-31)
          isToday: this.isSameDay(date, today),
          tasks: []
        });
      }
    },
    
    assignTasksToDays() {
      // First clear any existing tasks
      this.weekdays.forEach(day => {
        day.tasks = [];
      });
      
      // Then assign tasks to the appropriate day based on due date
      this.tasks.forEach(task => {
        const dueDate = task.due_date.split('T')[0]; // Get YYYY-MM-DD from ISO date
        
        // Find matching day
        const dayIndex = this.weekdays.findIndex(day => day.date === dueDate);
        if (dayIndex !== -1) {
          this.weekdays[dayIndex].tasks.push(task);
        }
      });
      
      // Sort tasks by priority (highest first)
      this.weekdays.forEach(day => {
        day.tasks.sort((a, b) => {
          const priorityOrder = {
            'Must be done': 0,
            'Important': 1,
            'Good to have': 2
          };
          
          return priorityOrder[a.priority] - priorityOrder[b.priority];
        });
      });
    },
    
    isSameDay(date1, date2) {
      return date1.getDate() === date2.getDate() &&
             date1.getMonth() === date2.getMonth() &&
             date1.getFullYear() === date2.getFullYear();
    },
    
    getTaskClasses(task) {
      // Combine priority and status for intuitive color coding
      return {
        'must-be-done': task.priority === 'Must be done',
        'important': task.priority === 'Important',
        'good-to-have': task.priority === 'Good to have',
        'to-do': task.status === 'To Do',
        'in-progress': task.status === 'In Progress',
        'under-review': task.status === 'Under Review',
        'completed': task.status === 'Completed'
      };
    }
  },
  computed: {
    weekRange() {
      if (this.weekdays.length === 0) return '';
      
      const firstDay = this.weekdays[0].date;
      const lastDay = this.weekdays[6].date;
      
      const formatDate = (dateStr) => {
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-US', { 
          month: 'short', 
          day: 'numeric'
        });
      };
      
      return `${formatDate(firstDay)} - ${formatDate(lastDay)}`;
    },
    
    // Filter and format weekdays for vertical list view
    weekdaysWithTasks() {
      return this.weekdays.map(day => {
        // Format full day name and date
        const date = new Date(day.date);
        
        return {
          ...day,
          fullName: date.toLocaleDateString('en-US', { weekday: 'long' }),
          formattedDate: date.toLocaleDateString('en-US', { 
            month: 'short', 
            day: 'numeric' 
          })
        };
      }).filter(day => {
        // Only show days with tasks or today
        return day.tasks.length > 0 || day.isToday;
      });
    }
  }
}
</script>

<style scoped>
.overview-container {
  padding: 1.5rem;
  color: #e0e0e0;
  background-color: #1f1f1f;
  transition: all 0.3s ease;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.overview-container.expanded {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 101;
  background-color: #1a1a1a;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
}

.overview-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

h2 {
  font-size: 1.1rem;
  font-weight: 500;
  margin: 0;
  letter-spacing: 0.5px;
}

.overview-subtitle {
  font-size: 0.75rem;
  color: #888;
  margin-top: 0.25rem;
  margin-bottom: 0;
}

.expand-btn {
  background: transparent;
  border: 1px solid #444;
  color: #aaa;
  width: 28px;
  height: 28px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  opacity: 0.8;
}

.expand-btn:hover {
  background-color: #333;
  color: white;
  opacity: 1;
}

/* Week list (compact view) */
.week-list {
  flex: 1;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  padding-right: 0.5rem;
}

.day-section {
  position: relative;
}

.current-day-section::before {
  content: '';
  position: absolute;
  left: -0.75rem;
  top: 0;
  bottom: 0;
  width: 2px;
  background-color: #8a56ff;
  opacity: 0.6;
  border-radius: 2px;
}

.day-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  padding-bottom: 0.5rem;
}

.day-name {
  font-size: 0.9rem;
  font-weight: 500;
  color: #ccc;
  letter-spacing: 0.3px;
}

.day-date {
  font-size: 0.7rem;
  color: #777;
}

.current-day-section .day-name {
  color: #8a56ff;
}

.no-tasks {
  font-size: 0.7rem;
  color: #666;
  font-style: italic;
  padding: 0.75rem 0;
  text-align: center;
  background-color: rgba(255, 255, 255, 0.02);
  border-radius: 4px;
}

.task-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.task-row {
  padding: 0.75rem 1rem;
  border-radius: 4px;
  position: relative;
  transition: all 0.2s ease;
  background-color: rgba(255, 255, 255, 0.03);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  border-left: 3px solid transparent;
}

.task-row:hover {
  transform: translateY(-2px);
  background-color: rgba(255, 255, 255, 0.04);
  box-shadow: 0 3px 6px rgba(0, 0, 0, 0.15);
}

.week-grid {
  height: calc(100% - 3rem);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.day-headers {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 0.5rem;
  margin-bottom: 0.75rem;
}

.day-label {
  text-align: center;
  padding: 0.5rem 0;
}

.day-name {
  display: block;
  font-size: 0.75rem;
  font-weight: 500;
  color: #bbb;
}

.day-date {
  display: block;
  font-size: 0.7rem;
  color: #777;
  margin-top: 0.15rem;
}

.current-day .day-name {
  color: #8a56ff;
}

.tasks-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 0.5rem;
  flex: 1;
  overflow-y: auto;
}

.day-column {
  background-color: rgba(255, 255, 255, 0.03);
  border-radius: 4px;
  padding: 0.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  min-height: 100%;
}

.day-column.current-day {
  background-color: rgba(138, 86, 255, 0.05);
}

.empty-day {
  flex: 1;
}

.task-item {
  padding: 0.5rem;
  border-radius: 3px;
  cursor: pointer;
  transition: transform 0.15s ease, box-shadow 0.15s ease;
  position: relative;
}

.task-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.15);
}

.task-title {
  font-size: 0.7rem;
  display: block;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Much better, intuitive color coding */
/* Common task styles for both task-item and task-row */
.task-item.must-be-done, .task-row.must-be-done {
  border-left-color: #ff5252;
}

.task-item.important, .task-row.important {
  border-left-color: #ffb142;
}

.task-item.good-to-have, .task-row.good-to-have {
  border-left-color: #2ecc71;
}

/* Completed task styling */
.task-item.completed, .task-row.completed {
  opacity: 0.7;
  text-decoration: line-through;
}

/* Status indicators */
.task-item.in-progress::after, .task-row.in-progress::after {
  content: '';
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background-color: #3498db;
}

.task-item.under-review::after, .task-row.under-review::after {
  content: '';
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background-color: #9b59b6;
}

.task-item.completed::after, .task-row.completed::after {
  content: '✓';
  position: absolute;
  top: 0.3rem;
  right: 0.5rem;
  font-size: 10px;
  color: #2ecc71;
}

/* Enhance task styling */
.task-title {
  font-size: 0.8rem;
  font-weight: 400;
  color: #e0e0e0;
  letter-spacing: 0.2px;
  line-height: 1.4;
}

/* Special styling for expanded view */
.task-item {
  background-color: rgba(255, 255, 255, 0.03);
  border-left: 3px solid transparent;
}

@media (max-width: 768px) {
  .overview-container.expanded {
    left: 0;
    right: 0;
    top: 0;
    border-radius: 0;
  }
}
</style>