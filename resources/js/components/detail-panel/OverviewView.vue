<template>
  <div class="progress-overview">
    <div class="focus-progress">
      <h2>Focus Progress</h2>
      <p class="progress-subtitle">Building mindful productivity</p>
      
      <!-- Time Range Selector -->
      <div class="time-range-selector">
        <button 
          v-for="range in timeRanges" 
          :key="range.value" 
          :class="['range-btn', { active: activeTimeRange === range.value }]"
          @click="activeTimeRange = range.value"
        >
          {{ range.label }}
        </button>
      </div>
      
      <!-- Today's Summary -->
      <div v-if="activeTimeRange === 'today'" class="time-summary">
        <div class="summary-header">
          <h3>Today's Focus</h3>
          <span class="date">{{ todayDate }}</span>
        </div>
        
        <div class="focus-stats">
          <div class="time-block">
            <span class="focus-time">{{ stats.today.totalTime }}</span>
            <span class="focus-goal">Goal: {{ stats.today.goal }}</span>
            <span class="focus-complete">{{ stats.today.percentage }}% Complete</span>
          </div>
          
          <div class="progress-circle">
            <svg viewBox="0 0 100 100">
              <circle cx="50" cy="50" r="45" fill="transparent" stroke="#2a2a2a" stroke-width="5" />
              <circle 
                cx="50" 
                cy="50" 
                r="45" 
                fill="transparent" 
                stroke="#8a56ff" 
                stroke-width="5" 
                stroke-dasharray="282.7"
                :stroke-dashoffset="getTodayOffset"
                transform="rotate(-90 50 50)"
              />
            </svg>
            <div class="progress-text">
              <span class="percentage">{{ stats.today.percentage }}%</span>
              <span class="label">of daily goal</span>
            </div>
          </div>
        </div>
        
        <div class="task-completion-list">
          <h4>Tasks Completed Today</h4>
          <div v-if="stats.today.completedTasks.length === 0" class="empty-list">
            No tasks completed yet today
          </div>
          <div v-else class="completed-tasks">
            <div v-for="(task, index) in stats.today.completedTasks" :key="index" class="completed-task-item">
              <div class="task-info">
                <span class="task-title">{{ task.title }}</span>
                <span class="task-project">{{ task.project }}</span>
              </div>
              <span class="task-time">{{ task.timeSpent }}</span>
            </div>
          </div>
        </div>
        
        <div class="due-today-list">
          <h4>Due Today</h4>
          <div v-if="stats.today.dueTasks.length === 0" class="empty-list">
            No tasks due today
          </div>
          <div v-else class="due-tasks">
            <div v-for="(task, index) in stats.today.dueTasks" :key="index" class="due-task-item">
              <div class="task-info">
                <span class="task-title">{{ task.title }}</span>
                <span :class="['task-status', 'status-' + task.status.toLowerCase().replace(' ', '-')]">
                  {{ task.status }}
                </span>
              </div>
              <span class="task-progress">{{ task.progress }}%</span>
            </div>
          </div>
        </div>
      </div>
      
      <!-- This Week's Summary -->
      <div v-if="activeTimeRange === 'week'" class="time-summary">
        <div class="summary-header">
          <h3>This Week's Focus</h3>
          <span class="date">{{ weekRange }}</span>
        </div>
        
        <div class="focus-stats">
          <div class="time-block">
            <span class="focus-time">{{ stats.week.totalTime }}</span>
            <span class="focus-goal">Goal: {{ stats.week.goal }}</span>
            <span class="focus-complete">{{ stats.week.percentage }}% Complete</span>
          </div>
          
          <div class="progress-circle">
            <svg viewBox="0 0 100 100">
              <circle cx="50" cy="50" r="45" fill="transparent" stroke="#2a2a2a" stroke-width="5" />
              <circle 
                cx="50" 
                cy="50" 
                r="45" 
                fill="transparent" 
                stroke="#8a56ff" 
                stroke-width="5" 
                stroke-dasharray="282.7"
                :stroke-dashoffset="getWeekOffset"
                transform="rotate(-90 50 50)"
              />
            </svg>
            <div class="progress-text">
              <span class="percentage">{{ stats.week.percentage }}%</span>
              <span class="label">of weekly goal</span>
            </div>
          </div>
        </div>
        
        <div class="weekly-overview">
          <h4>Daily Breakdown</h4>
          <div class="chart-container">
            <div class="chart-bars">
              <div 
                v-for="(day, index) in stats.week.dailyBreakdown" 
                :key="index" 
                class="chart-bar" 
                :style="{ height: day.percentage + '%' }"
              >
                <span class="bar-tooltip">{{ day.hours }}</span>
              </div>
            </div>
            <div class="chart-labels">
              <span v-for="(day, index) in stats.week.dailyBreakdown" :key="index">
                {{ day.label }}
              </span>
            </div>
          </div>
        </div>
        
        <div class="focus-metrics">
          <div class="metric-card">
            <h4>Tasks Completed</h4>
            <div class="metric-value">{{ stats.week.tasksCompleted }}</div>
          </div>
          
          <div class="metric-card">
            <h4>Avg. Focus per Day</h4>
            <div class="metric-value">{{ stats.week.avgFocusPerDay }}</div>
          </div>
        </div>
      </div>
      
      <!-- This Month's Summary -->
      <div v-if="activeTimeRange === 'month'" class="time-summary">
        <div class="summary-header">
          <h3>This Month's Focus</h3>
          <span class="date">{{ monthName }}</span>
        </div>
        
        <div class="focus-stats">
          <div class="time-block">
            <span class="focus-time">{{ stats.month.totalTime }}</span>
            <span class="focus-goal">Goal: {{ stats.month.goal }}</span>
            <span class="focus-complete">{{ stats.month.percentage }}% Complete</span>
          </div>
          
          <div class="progress-circle">
            <svg viewBox="0 0 100 100">
              <circle cx="50" cy="50" r="45" fill="transparent" stroke="#2a2a2a" stroke-width="5" />
              <circle 
                cx="50" 
                cy="50" 
                r="45" 
                fill="transparent" 
                stroke="#8a56ff" 
                stroke-width="5" 
                stroke-dasharray="282.7"
                :stroke-dashoffset="getMonthOffset"
                transform="rotate(-90 50 50)"
              />
            </svg>
            <div class="progress-text">
              <span class="percentage">{{ stats.month.percentage }}%</span>
              <span class="label">of monthly goal</span>
            </div>
          </div>
        </div>
        
        <div class="monthly-heatmap">
          <h4>Focus Intensity</h4>
          <div class="heatmap-container">
            <div 
              v-for="(day, index) in stats.month.heatmap" 
              :key="index" 
              class="heatmap-day" 
              :class="'intensity-' + day.intensity"
              :title="day.date + ': ' + day.hours"
            ></div>
          </div>
        </div>
        
        <div class="monthly-metrics">
          <div class="metric-card">
            <h4>Focus Days</h4>
            <div class="metric-value">{{ stats.month.focusDays }}</div>
            <div class="metric-label">out of {{ stats.month.totalDays }}</div>
          </div>
          
          <div class="metric-card">
            <h4>Most Focused Day</h4>
            <div class="metric-value">{{ stats.month.mostFocusedDay.date }}</div>
            <div class="metric-label">{{ stats.month.mostFocusedDay.hours }}</div>
          </div>
          
          <div class="metric-card">
            <h4>Tasks Completed</h4>
            <div class="metric-value">{{ stats.month.tasksCompleted }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'OverviewView',
  props: {
    stats: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      activeTimeRange: 'today', // Default to weekly view
      timeRanges: [
        { value: 'today', label: 'Today' },
        { value: 'week', label: 'This Week' },
        { value: 'month', label: 'This Month' }
      ]
    }
  },
  computed: {
    getTodayOffset() {
      const circumference = 2 * Math.PI * 45;
      return circumference - (circumference * this.stats.today.percentage / 100);
    },
    getWeekOffset() {
      const circumference = 2 * Math.PI * 45;
      return circumference - (circumference * this.stats.week.percentage / 100);
    },
    getMonthOffset() {
      const circumference = 2 * Math.PI * 45;
      return circumference - (circumference * this.stats.month.percentage / 100);
    },
    todayDate() {
      return new Date().toLocaleDateString(undefined, { 
        weekday: 'long', 
        month: 'long', 
        day: 'numeric' 
      });
    },
    weekRange() {
      const today = new Date();
      const startOfWeek = new Date(today);
      const day = startOfWeek.getDay();
      const diff = startOfWeek.getDate() - day + (day === 0 ? -6 : 1); // Adjust to Monday as first day
      startOfWeek.setDate(diff);
      
      const endOfWeek = new Date(startOfWeek);
      endOfWeek.setDate(endOfWeek.getDate() + 6);
      
      return `${startOfWeek.toLocaleDateString(undefined, { month: 'short', day: 'numeric' })} - ${endOfWeek.toLocaleDateString(undefined, { month: 'short', day: 'numeric' })}`;
    },
    monthName() {
      return new Date().toLocaleDateString(undefined, { month: 'long', year: 'numeric' });
    }
  }
}
</script>

<style scoped>
/* Progress Overview */
.progress-overview {
  padding: 1.25rem;
  overflow-y: auto;
}

.focus-progress h2 {
  font-size: 1.2rem;
  font-weight: 500;
  margin-bottom: 0.25rem;
  margin-top: 0;
  letter-spacing: 0.5px;
}

.progress-subtitle {
  color: #999;
  margin-bottom: 1.25rem;
  font-size: 0.8rem;
}

/* Time Range Selector */
.time-range-selector {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
  background-color: #252525;
  padding: 0.4rem;
  border-radius: 6px;
}

.range-btn {
  flex: 1;
  padding: 0.5rem 0;
  border: none;
  background-color: transparent;
  color: #999;
  font-size: 0.8rem;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.15s ease;
}

.range-btn:hover {
  color: #fff;
}

.range-btn.active {
  background-color: #2a2a2a;
  color: #fff;
}

/* Time Summary (Common Elements) */
.time-summary {
  margin-bottom: 1rem;
}

.summary-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.summary-header h3 {
  font-size: 1rem;
  font-weight: 500;
  margin: 0;
  letter-spacing: 0.5px;
}

.summary-header .date {
  font-size: 0.8rem;
  color: #999;
}

.focus-stats {
  display: flex;
  margin-bottom: 1.5rem;
  padding: 1.25rem;
  background-color: #252525;
  border-radius: 6px;
}

.time-block {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.focus-time {
  font-size: 1.8rem;
  font-weight: 500;
}

.focus-goal {
  font-size: 0.75rem;
  color: #999;
}

.focus-complete {
  font-size: 0.75rem;
  color: #ccc;
}

.progress-circle {
  position: relative;
  width: 120px;
  height: 120px;
}

.progress-circle svg {
  width: 100%;
  height: 100%;
}

.progress-text {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
}

.progress-text .percentage {
  font-size: 1.2rem;
  font-weight: 500;
  display: block;
}

.progress-text .label {
  font-size: 0.7rem;
  color: #999;
}

/* Today View Specific */
.task-completion-list,
.due-today-list {
  background-color: #252525;
  padding: 1.25rem;
  border-radius: 6px;
  margin-bottom: 1.5rem;
}

.task-completion-list h4,
.due-today-list h4 {
  font-size: 0.9rem;
  margin-top: 0;
  margin-bottom: 1rem;
  font-weight: 500;
  letter-spacing: 0.5px;
}

.empty-list {
  color: #999;
  font-style: italic;
  font-size: 0.8rem;
  text-align: center;
  padding: 0.5rem 0;
}

.completed-task-item,
.due-task-item {
  display: flex;
  justify-content: space-between;
  padding: 0.75rem;
  background-color: #2a2a2a;
  border-radius: 6px;
  margin-bottom: 0.5rem;
}

.task-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.task-title {
  font-size: 0.85rem;
  font-weight: 500;
}

.task-project {
  font-size: 0.7rem;
  color: #999;
}

.task-time {
  font-size: 0.8rem;
  color: #8a56ff;
  font-weight: 500;
}

.task-status {
  font-size: 0.7rem;
  padding: 0.1rem 0.5rem;
  border-radius: 10px;
  display: inline-block;
}

.status-to-do {
  color: #999;
  background-color: rgba(153, 153, 153, 0.1);
}

.status-in-progress {
  color: #3498db;
  background-color: rgba(52, 152, 219, 0.1);
}

.status-under-review {
  color: #9b59b6;
  background-color: rgba(155, 89, 182, 0.1);
}

.status-completed {
  color: #2ecc71;
  background-color: rgba(46, 204, 113, 0.1);
}

.task-progress {
  font-size: 0.8rem;
  font-weight: 500;
}

/* Week View Specific */
.weekly-overview {
  background-color: #252525;
  padding: 1.25rem;
  border-radius: 6px;
  margin-bottom: 1.5rem;
}

.weekly-overview h4 {
  font-size: 0.9rem;
  margin-top: 0;
  margin-bottom: 1rem;
  font-weight: 500;
  letter-spacing: 0.5px;
}

.chart-container {
  height: 180px;
}

.chart-bars {
  display: flex;
  height: 150px;
  align-items: flex-end;
  gap: 0.6rem;
  margin-bottom: 0.4rem;
}

.chart-bar {
  flex: 1;
  background: linear-gradient(to top, #8a56ff, #e040fb);
  border-radius: 4px 4px 0 0;
  position: relative;
  transition: all 0.3s ease;
}

.chart-bar:hover {
  transform: translateY(-5px);
}

.bar-tooltip {
  position: absolute;
  top: -25px;
  left: 50%;
  transform: translateX(-50%);
  background-color: #333;
  color: #fff;
  font-size: 0.7rem;
  padding: 0.2rem 0.5rem;
  border-radius: 4px;
  white-space: nowrap;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.chart-bar:hover .bar-tooltip {
  opacity: 1;
}

.chart-labels {
  display: flex;
  gap: 0.6rem;
}

.chart-labels span {
  flex: 1;
  text-align: center;
  font-size: 0.7rem;
  color: #999;
}

.focus-metrics {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.metric-card {
  background-color: #252525;
  padding: 1rem;
  border-radius: 6px;
  text-align: center;
}

.metric-card h4 {
  font-size: 0.8rem;
  color: #999;
  margin-top: 0;
  margin-bottom: 0.4rem;
  font-weight: normal;
  letter-spacing: 0.5px;
}

.metric-card .metric-value {
  font-size: 1.2rem;
  font-weight: 500;
}

.metric-card .metric-label {
  font-size: 0.7rem;
  color: #999;
  margin-top: 0.2rem;
}

/* Month View Specific */
.monthly-heatmap {
  background-color: #252525;
  padding: 1.25rem;
  border-radius: 6px;
  margin-bottom: 1.5rem;
}

.monthly-heatmap h4 {
  font-size: 0.9rem;
  margin-top: 0;
  margin-bottom: 1rem;
  font-weight: 500;
  letter-spacing: 0.5px;
}

.heatmap-container {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 0.3rem;
}

.heatmap-day {
  width: 100%;
  aspect-ratio: 1;
  border-radius: 2px;
  transition: transform 0.15s ease;
}

.heatmap-day:hover {
  transform: scale(1.2);
}

.intensity-0 {
  background-color: #2a2a2a;
}

.intensity-1 {
  background-color: rgba(138, 86, 255, 0.2);
}

.intensity-2 {
  background-color: rgba(138, 86, 255, 0.4);
}

.intensity-3 {
  background-color: rgba(138, 86, 255, 0.6);
}

.intensity-4 {
  background-color: rgba(138, 86, 255, 0.9);
}

.monthly-metrics {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
}

@media (max-width: 768px) {
  .focus-stats {
    flex-direction: column;
    gap: 1rem;
  }
  
  .monthly-metrics {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .monthly-metrics .metric-card:last-child {
    grid-column: span 2;
  }
}
</style>