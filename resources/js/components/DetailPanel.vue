<template>
  <div class="detail-panel">
    <!-- Zen UI UX Tabs for switching between views -->
    <div class="zen-tabs">
      <button 
        class="zen-tab" 
        :class="{ active: activeTab === 'overview' }" 
        @click="switchToOverview"
      >
        <span class="zen-tab-icon">
          <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none">
            <rect x="3" y="3" width="7" height="7"></rect>
            <rect x="14" y="3" width="7" height="7"></rect>
            <rect x="14" y="14" width="7" height="7"></rect>
            <rect x="3" y="14" width="7" height="7"></rect>
          </svg>
        </span>
        Overview
      </button>
      <button 
        class="zen-tab" 
        :class="{ active: activeTab === 'task', disabled: !selectedTask }" 
        :disabled="!selectedTask" 
        @click="switchToTask"
      >
        <span class="zen-tab-icon">
          <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
            <line x1="16" y1="13" x2="8" y2="13"></line>
            <line x1="16" y1="17" x2="8" y2="17"></line>
            <polyline points="10 9 9 9 8 9"></polyline>
          </svg>
        </span>
        Task
      </button>
    </div>
    
    <div v-if="selectedTask && activeTab === 'task'" class="task-detail-view">
      <!-- Task Detail Header -->
      <DetailHeader 
        :task="selectedTask"
        :is-mobile="isMobile"
        :is-timer-running="isTimerRunning"
        :time-spent="timeSpent"
        @close="$emit('close-task')"
        @complete="$emit('complete-task', selectedTask)"
        @toggle-timer="toggleTimer"
      />
      
      <!-- Task Details -->
      <TaskDetails 
        :task="selectedTask"
        :time-spent="timeSpent"
        :is-timer-running="isTimerRunning"
        :goal-time="goalTime"
        @status-updated="handleStatusUpdate"
        @task-updated="handleTaskUpdate"
      />
      
      <!-- Comments Section -->
      <CommentSection 
        :comments="comments"
        :task-id="selectedTask ? selectedTask.id : 0"
        :task-title="selectedTask ? selectedTask.title : ''"
        @add-comment="$emit('add-comment', $event)"
      />
    </div>
    
    <!-- Progress Overview Component -->
    <OverviewView
      v-if="activeTab === 'overview'"
      :stats="stats"
    />
  </div>
</template>

<script>
import DetailHeader from './detail-panel/DetailHeader.vue';
import TaskDetails from './detail-panel/TaskDetails.vue';
import CommentSection from './detail-panel/CommentSection.vue';
import OverviewView from './detail-panel/OverviewView.vue';
import taskService from '../services/taskService';
import projectService from '../services/projectService';

export default {
  name: 'DetailPanel',
  components: {
    DetailHeader,
    TaskDetails,
    CommentSection,
    OverviewView
  },
  props: {
    selectedTask: {
      type: Object,
      default: null
    },
    comments: {
      type: Array,
      default: () => []
    },
    isMobile: {
      type: Boolean,
      default: false
    }
  },
  emits: ['close-task', 'complete-task', 'add-comment', 'update-task'],
  data() {
    return {
      activeTab: 'overview', // Default to overview tab
      timeSpent: 0,
      isTimerRunning: false,
      timerInterval: null,
      goalTime: 6 * 60 * 60, // 6 hours in seconds
      isLoading: false,
      projects: [],
      allTasks: [],
      completedTasks: [],
      dueTasks: [],
      stats: {
        today: {
          totalTime: '0h 0m',
          goal: '6h 0m',
          percentage: 0,
          completedTasks: [],
          dueTasks: []
        },
        week: {
          totalTime: '0h 0m',
          goal: '30h 0m',
          percentage: 0,
          tasksCompleted: 0,
          avgFocusPerDay: '0h 0m',
          dailyBreakdown: [
            { label: 'Mon', hours: '0h 0m', percentage: 0 },
            { label: 'Tue', hours: '0h 0m', percentage: 0 },
            { label: 'Wed', hours: '0h 0m', percentage: 0 },
            { label: 'Thu', hours: '0h 0m', percentage: 0 },
            { label: 'Fri', hours: '0h 0m', percentage: 0 },
            { label: 'Sat', hours: '0h 0m', percentage: 0 },
            { label: 'Sun', hours: '0h 0m', percentage: 0 }
          ]
        },
        month: {
          totalTime: '0h 0m',
          goal: '120h 0m',
          percentage: 0,
          focusDays: 0,
          totalDays: 30,
          tasksCompleted: 0,
          mostFocusedDay: {
            date: '',
            hours: '0h 0m'
          },
          heatmap: []
        }
      }
    }
  },
  methods: {
    switchToOverview() {
      this.activeTab = 'overview';
      this.fetchData();
    },
    switchToTask() {
      if (this.selectedTask) {
        this.activeTab = 'task';
      }
    },
    toggleTimer() {
      if (this.isTimerRunning) {
        // Pause timer
        clearInterval(this.timerInterval);
        this.isTimerRunning = false;
      } else {
        // Start timer
        this.isTimerRunning = true;
        this.timerInterval = setInterval(() => {
          this.timeSpent += 1; // Increment by 1 second
        }, 1000);
      }
    },
    resetTimer() {
      this.timeSpent = 0;
      this.isTimerRunning = false;
      clearInterval(this.timerInterval);
    },
    handleStatusUpdate(updatedTask) {
      // Emit event to parent component
      this.$emit('update-task', updatedTask);
      
      // Don't try to update selectedTask directly as it's a prop
      // The parent component will update it with the emitted event
      
      // If task is marked as completed, emit the complete-task event too
      if (updatedTask.status === 'Completed') {
        this.$emit('complete-task', updatedTask);
      }
    },
    handleTaskUpdate(updatedTask) {
      // This method handles task updates from the TaskDetails component
      // Emit event to parent component to update the task
      this.$emit('update-task', updatedTask);
    },
    async fetchData() {
      try {
        this.isLoading = true;
        await Promise.all([
          this.fetchProjects(),
          this.fetchAllTasks()
        ]);
        this.processTaskData();
      } catch (error) {
        console.error('Error fetching data:', error);
      } finally {
        this.isLoading = false;
      }
    },
    async fetchProjects() {
      try {
        const response = await projectService.getAll();
        this.projects = response.data.data || response.data;
      } catch (error) {
        console.error('Error fetching projects:', error);
      }
    },
    async fetchAllTasks() {
      try {
        const response = await taskService.getAll();
        this.allTasks = response.data.data || response.data;
      } catch (error) {
        console.error('Error fetching tasks:', error);
      }
    },
    processTaskData() {
      // Process tasks for today's view
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      
      // Filter completed tasks for today
      this.completedTasks = this.allTasks.filter(task => 
        task.status === 'Completed' && 
        new Date(task.updated_at) >= today
      );
      
      // Filter due tasks for today
      this.dueTasks = this.allTasks.filter(task => {
        const dueDate = new Date(task.due_date);
        dueDate.setHours(0, 0, 0, 0);
        return task.status !== 'Completed' && dueDate.getTime() <= today.getTime();
      });
      
      // Update stats for today
      this.stats.today.completedTasks = this.completedTasks.map(task => {
        const project = this.projects.find(p => p.id === task.project_id);
        return {
          title: task.title,
          project: project ? project.title : 'Unknown Project',
          timeSpent: this.getRandomTimeString() // In a real app, you'd track actual time spent
        };
      });
      
      this.stats.today.dueTasks = this.dueTasks.map(task => {
        return {
          title: task.title,
          status: task.status,
          progress: this.calculateTaskProgress(task)
        };
      });
      
      // Calculate time metrics (simulated)
      this.stats.today.totalTime = this.calcTotalTimeForTasks(this.completedTasks);
      const todayGoalSeconds = 6 * 60 * 60; // 6 hours
      const todayTotalSeconds = this.convertTimeStringToSeconds(this.stats.today.totalTime);
      this.stats.today.percentage = Math.round((todayTotalSeconds / todayGoalSeconds) * 100);
      
      // Process tasks for week's view
      const startOfWeek = new Date(today);
      const day = startOfWeek.getDay();
      const diff = startOfWeek.getDate() - day + (day === 0 ? -6 : 1); // Adjust to Monday
      startOfWeek.setDate(diff);
      
      const weekTasks = this.allTasks.filter(task => 
        task.status === 'Completed' && 
        new Date(task.updated_at) >= startOfWeek
      );
      
      this.stats.week.tasksCompleted = weekTasks.length;
      this.stats.week.totalTime = this.calcTotalTimeForTasks(weekTasks);
      const weekGoalSeconds = 30 * 60 * 60; // 30 hours
      const weekTotalSeconds = this.convertTimeStringToSeconds(this.stats.week.totalTime);
      this.stats.week.percentage = Math.round((weekTotalSeconds / weekGoalSeconds) * 100);
      
      // Calculate average focus per day
      const daysSinceStartOfWeek = Math.min(7, Math.floor((today - startOfWeek) / (1000 * 60 * 60 * 24)) + 1);
      const avgSeconds = weekTotalSeconds / daysSinceStartOfWeek;
      this.stats.week.avgFocusPerDay = this.formatTimeFromSeconds(avgSeconds);
      
      // Generate daily breakdown (simulated for demo)
      const daysOfWeek = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
      this.stats.week.dailyBreakdown = daysOfWeek.map((label, index) => {
        // For past days, generate realistic data; for future days, show zeros
        if (index < daysSinceStartOfWeek) {
          const hours = Math.floor(Math.random() * 5) + (index === daysSinceStartOfWeek - 1 ? 1 : 2);
          const minutes = Math.floor(Math.random() * 60);
          const percentage = Math.min(100, Math.round((hours / 6) * 100));
          return {
            label,
            hours: `${hours}h ${minutes}m`,
            percentage
          };
        } else {
          return { label, hours: '0h 0m', percentage: 0 };
        }
      });
      
      // Process tasks for month's view
      const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
      const monthTasks = this.allTasks.filter(task => 
        task.status === 'Completed' && 
        new Date(task.updated_at) >= startOfMonth
      );
      
      this.stats.month.tasksCompleted = monthTasks.length;
      this.stats.month.totalTime = this.calcTotalTimeForTasks(monthTasks, 50);
      const monthGoalSeconds = 120 * 60 * 60; // 120 hours
      const monthTotalSeconds = this.convertTimeStringToSeconds(this.stats.month.totalTime);
      this.stats.month.percentage = Math.round((monthTotalSeconds / monthGoalSeconds) * 100);
      
      // Calculate focus days (simulated)
      const today_date = today.getDate();
      this.stats.month.focusDays = Math.min(today_date, Math.round(monthTasks.length * 0.8));
      
      // Generate most focused day (simulated)
      const randomDay = Math.floor(Math.random() * today_date) + 1;
      const month = today.toLocaleDateString(undefined, { month: 'short' });
      const randomHours = Math.floor(Math.random() * 3) + 4; // 4-6 hours
      const randomMinutes = Math.floor(Math.random() * 60);
      this.stats.month.mostFocusedDay = {
        date: `${month} ${randomDay}`,
        hours: `${randomHours}h ${randomMinutes}m`
      };
      
      // Generate heatmap data
      const daysInMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0).getDate();
      this.stats.month.heatmap = Array.from({ length: daysInMonth }, (_, i) => {
        const date = `${month} ${i + 1}`;
        
        if (i + 1 > today_date) {
          // Future days have no data
          return { date, hours: '0h 0m', intensity: 0 };
        } else {
          // Past days have simulated data
          // More intense activity on weekdays, less on weekends
          const dayOfWeek = new Date(today.getFullYear(), today.getMonth(), i + 1).getDay();
          const isWeekend = dayOfWeek === 0 || dayOfWeek === 6;
          const maxIntensity = isWeekend ? 2 : 4;
          const intensity = Math.floor(Math.random() * (maxIntensity + 1));
          const hours = intensity === 0 ? 0 : Math.floor(Math.random() * 4) + (intensity > 2 ? 3 : 1);
          const minutes = Math.floor(Math.random() * 60);
          
          return {
            date,
            hours: `${hours}h ${minutes}m`,
            intensity
          };
        }
      });
    },
    calculateTaskProgress(task) {
      // In a real app, this would calculate actual progress
      // For demo purposes, we'll simulate based on status
      switch (task.status) {
        case 'To Do':
          return 0;
        case 'In Progress':
          return Math.floor(Math.random() * 50) + 20; // 20-70%
        case 'Under Review':
          return Math.floor(Math.random() * 20) + 75; // 75-95%
        case 'Completed':
          return 100;
        default:
          return 0;
      }
    },
    getRandomTimeString() {
      // Generate a random time for demo purposes
      const hours = Math.floor(Math.random() * 2) + 1;
      const minutes = Math.floor(Math.random() * 60);
      return `${hours}h ${minutes}m`;
    },
    calcTotalTimeForTasks(tasks, multiplier = 1) {
      // In a real app, you'd sum actual time tracked
      // For demo, we'll calculate based on task count and a multiplier
      const totalMinutes = tasks.length * 45 * multiplier; // Assume avg 45 min per task
      const hours = Math.floor(totalMinutes / 60);
      const minutes = totalMinutes % 60;
      return `${hours}h ${minutes}m`;
    },
    convertTimeStringToSeconds(timeString) {
      // Convert a time string like "2h 30m" to seconds
      const regex = /(\d+)h\s+(\d+)m/;
      const match = timeString.match(regex);
      if (match) {
        const hours = parseInt(match[1], 10);
        const minutes = parseInt(match[2], 10);
        return (hours * 60 * 60) + (minutes * 60);
      }
      return 0;
    },
    formatTimeFromSeconds(seconds) {
      // Format seconds to "2h 30m" format
      const hours = Math.floor(seconds / 3600);
      const minutes = Math.floor((seconds % 3600) / 60);
      return `${hours}h ${minutes}m`;
    }
  },
  watch: {
    selectedTask(newTask) {
      // When a task is selected, switch to the task tab
      if (newTask) {
        this.activeTab = 'task';
        // Reset timer when changing tasks
        this.resetTimer();
      } else {
        // When task is deselected, switch to overview
        this.activeTab = 'overview';
      }
    }
  },
  beforeUnmount() {
    // Clear any running timers
    if (this.timerInterval) {
      clearInterval(this.timerInterval);
    }
  },
  mounted() {
    // Fetch data when component is mounted
    this.fetchData();
  }
}
</script>

<style scoped>
.detail-panel {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  background-color: #1a1a1a;
  overflow-y: auto;
}

/* Zen UI UX Tabs */
.zen-tabs {
  display: flex;
  padding: 0.5rem;
  background-color: #252525;
  border-bottom: 1px solid #333;
  position: sticky;
  top: 0;
  z-index: 10;
}

.zen-tab {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.6rem 1.2rem;
  border: none;
  background-color: transparent;
  color: #999;
  font-size: 0.85rem;
  font-weight: 500;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.15s ease;
  letter-spacing: 0.5px;
}

.zen-tab:hover:not(.disabled) {
  color: #fff;
  background-color: rgba(138, 86, 255, 0.1);
}

.zen-tab.active {
  color: #fff;
  background-color: rgba(138, 86, 255, 0.2);
}

.zen-tab.disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.zen-tab-icon {
  display: flex;
  align-items: center;
}

.task-detail-view {
  padding: 1.25rem;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}
</style>