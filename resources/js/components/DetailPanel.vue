<!-- resources/js/components/DetailPanel.vue -->
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
    
    <!-- Task Detail View Component -->
    <TaskView
      v-if="selectedTask && activeTab === 'task'"
      :task="selectedTask"
      :comments="comments"
      :isMobile="isMobile"
      @close-task="$emit('close-task')"
      @complete-task="$emit('complete-task', $event)"
      @add-comment="$emit('add-comment', $event)"
      @timer-update="handleTimerUpdate"
      ref="taskView"
    />
    
    <!-- Progress Overview Component -->
    <OverviewView
      v-if="activeTab === 'overview'"
      :stats="dummyStats"
    />
  </div>
</template>

<script>
import TaskView from './detail-panel/TaskView.vue';
import OverviewView from './detail-panel/OverviewView.vue';

export default {
  name: 'DetailPanel',
  components: {
    TaskView,
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
  emits: ['close-task', 'complete-task', 'add-comment'],
  data() {
    return {
      activeTab: 'overview', // Default to overview tab
      // Dummy data for demonstration
      dummyStats: {
        today: {
          totalTime: '2h 49m',
          goal: '6h 0m',
          percentage: 46,
          completedTasks: [
            { title: 'Complete Project Proposal', project: 'Project Management App', timeSpent: '1h 25m' },
            { title: 'Review Design Mockups', project: 'Website Redesign', timeSpent: '45m' },
            { title: 'Client Meeting Notes', project: 'Project Management App', timeSpent: '39m' }
          ],
          dueTasks: [
            { title: 'Finalize Contract', status: 'In Progress', progress: 65 },
            { title: 'Backend API Documentation', status: 'To Do', progress: 0 },
            { title: 'Team Retrospective', status: 'In Progress', progress: 30 }
          ]
        },
        week: {
          totalTime: '16h 42m',
          goal: '30h 0m',
          percentage: 55,
          tasksCompleted: 12,
          avgFocusPerDay: '2h 23m',
          dailyBreakdown: [
            { label: 'Mon', hours: '2h 30m', percentage: 40 },
            { label: 'Tue', hours: '4h 10m', percentage: 75 },
            { label: 'Wed', hours: '3h 45m', percentage: 60 },
            { label: 'Thu', hours: '2h 15m', percentage: 35 },
            { label: 'Fri', hours: '1h 50m', percentage: 30 },
            { label: 'Sat', hours: '1h 5m', percentage: 15 },
            { label: 'Sun', hours: '0h 45m', percentage: 10 }
          ]
        },
        month: {
          totalTime: '64h 30m',
          goal: '120h 0m',
          percentage: 53,
          focusDays: 18,
          totalDays: 30,
          tasksCompleted: 42,
          mostFocusedDay: {
            date: 'Mar 15',
            hours: '5h 45m'
          },
          heatmap: Array.from({ length: 30 }, (_, i) => ({
            date: `Mar ${i + 1}`,
            hours: `${Math.floor(Math.random() * 6)}h ${Math.floor(Math.random() * 60)}m`,
            intensity: Math.floor(Math.random() * 5) // 0-4, where 0 is no activity, 4 is intense
          }))
        }
      }
    }
  },
  methods: {
    switchToOverview() {
      this.activeTab = 'overview';
    },
    switchToTask() {
      if (this.selectedTask) {
        this.activeTab = 'task';
      }
    },
    handleTimerUpdate(timeSpent) {
      // Handle timer updates from the TaskView component if needed
      // This could be used to update statistics etc.
    }
  },
  watch: {
    selectedTask(newTask) {
      // When a task is selected, switch to the task tab
      if (newTask) {
        this.activeTab = 'task';
        // If we have a reference to the task view component, reset its timer
        if (this.$refs.taskView) {
          this.$refs.taskView.resetTimer();
        }
      } else {
        // When task is deselected, switch to overview
        this.activeTab = 'overview';
      }
    }
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
</style>