<template>
  <div class="overview-container">
    <h2>This Week</h2>
    <p class="overview-subtitle">{{ weekRange }}</p>
    
    <div class="week-grid">
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
            :class="[`priority-${getPriorityClass(task.priority)}`, `status-${getStatusClass(task.status)}`]"
          >
            <span class="task-title">{{ task.title }}</span>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Legend -->
    <div class="task-legend">
      <div class="legend-priority">
        <div class="legend-item">
          <span class="legend-color priority-must-be-done"></span>
          <span class="legend-text">Must be done</span>
        </div>
        <div class="legend-item">
          <span class="legend-color priority-important"></span>
          <span class="legend-text">Important</span>
        </div>
        <div class="legend-item">
          <span class="legend-color priority-good-to-have"></span>
          <span class="legend-text">Good to have</span>
        </div>
      </div>
      
      <div class="legend-status">
        <div class="legend-item">
          <span class="legend-dot status-to-do"></span>
          <span class="legend-text">To Do</span>
        </div>
        <div class="legend-item">
          <span class="legend-dot status-in-progress"></span>
          <span class="legend-text">In Progress</span>
        </div>
        <div class="legend-item">
          <span class="legend-dot status-under-review"></span>
          <span class="legend-text">Under Review</span>
        </div>
        <div class="legend-item">
          <span class="legend-dot status-completed"></span>
          <span class="legend-text">Completed</span>
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
      weekdays: []
    }
  },
  mounted() {
    this.fetchTasks();
    this.generateWeekDays();
  },
  methods: {
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
    
    getPriorityClass(priority) {
      return priority.toLowerCase().replace(/\s+/g, '-');
    },
    
    getStatusClass(status) {
      return status.toLowerCase().replace(/\s+/g, '-');
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
    }
  }
}
</script>

<style scoped>
.overview-container {
  padding: 2rem;
  height: 100%;
  overflow-y: auto;
  color: #e0e0e0;
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
  margin-bottom: 2rem;
}

.week-grid {
  margin-bottom: 2rem;
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
  min-height: 300px;
}

.day-column {
  background-color: rgba(255, 255, 255, 0.02);
  border-radius: 4px;
  padding: 0.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.day-column.current-day {
  background-color: rgba(138, 86, 255, 0.05);
}

.empty-day {
  flex: 1;
}

.task-item {
  background-color: #2a2a2a;
  border-radius: 3px;
  padding: 0.5rem;
  border-left: 3px solid;
  cursor: pointer;
  transition: transform 0.15s ease, opacity 0.15s ease;
}

.task-item:hover {
  transform: translateY(-2px);
  opacity: 0.9;
}

.task-title {
  font-size: 0.7rem;
  display: block;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Priority colors */
.priority-must-be-done {
  border-left-color: #ff5252;
}

.priority-important {
  border-left-color: #ffb142;
}

.priority-good-to-have {
  border-left-color: #2ecc71;
}

/* Status styles */
.status-to-do {
  background-color: #2a2a2a;
}

.status-in-progress {
  background-color: rgba(52, 152, 219, 0.15);
}

.status-under-review {
  background-color: rgba(155, 89, 182, 0.15);
}

.status-completed {
  background-color: rgba(46, 204, 113, 0.15);
}

/* Legend */
.task-legend {
  display: flex;
  justify-content: space-between;
  padding: 1rem;
  background-color: rgba(255, 255, 255, 0.02);
  border-radius: 4px;
}

.legend-priority, .legend-status {
  display: flex;
  gap: 1rem;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 0.4rem;
}

.legend-color {
  width: 12px;
  height: 12px;
  border-radius: 2px;
}

.legend-color.priority-must-be-done {
  background-color: #ff5252;
}

.legend-color.priority-important {
  background-color: #ffb142;
}

.legend-color.priority-good-to-have {
  background-color: #2ecc71;
}

.legend-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
}

.legend-dot.status-to-do {
  background-color: #aaa;
}

.legend-dot.status-in-progress {
  background-color: #3498db;
}

.legend-dot.status-under-review {
  background-color: #9b59b6;
}

.legend-dot.status-completed {
  background-color: #2ecc71;
}

.legend-text {
  font-size: 0.65rem;
  color: #999;
}

@media (max-width: 768px) {
  .task-legend {
    flex-direction: column;
    gap: 1rem;
  }
  
  .legend-priority, .legend-status {
    justify-content: space-between;
  }
}
</style>