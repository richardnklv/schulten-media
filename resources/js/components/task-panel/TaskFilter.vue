<!-- resources/js/components/task-panel/TaskFilter.vue -->
<template>
  <div class="task-filter">
    <div class="filters-container">
      <!-- Status filter -->
      <div class="status-filter">
        <button 
          v-for="status in statuses" 
          :key="status.value"
          :class="['status-btn', { active: selectedStatus === status.value }]"
          @click="selectStatus(status.value, $event)"
        >
          {{ status.label }}
        </button>
      </div>
      
      <!-- Time filter toggle -->
      <button 
        class="time-filter-toggle" 
        @click="toggleTimeFilter"
        :class="{ active: timeFilterActive }"
      >
        <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2" fill="none">
          <circle cx="12" cy="12" r="10"></circle>
          <polyline points="12 6 12 12 16 14"></polyline>
        </svg>
        <span>{{ selectedTimeRange }}</span>
      </button>
    </div>
    
    <!-- Time filter popup -->
    <div class="time-filter-popup" v-if="showTimeFilterPopup">
      <div 
        v-for="timeRange in timeRanges" 
        :key="timeRange.value"
        class="time-option"
        :class="{ active: selectedTimeRange === timeRange.label }"
        @click="selectTimeRange(timeRange.value, timeRange.label)"
      >
        {{ timeRange.label }}
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'TaskFilter',
  props: {
    statuses: {
      type: Array,
      default: () => [
        { value: 'all', label: 'All' },
        { value: 'To Do', label: 'To Do' },
        { value: 'In Progress', label: 'In Progress' },
        { value: 'Completed', label: 'Done' }
      ]
    },
    selectedStatus: {
      type: String,
      default: 'all'
    }
  },
  data() {
    return {
      showTimeFilterPopup: false,
      timeFilterActive: false,
      selectedTimeRange: 'All time',
      timeRanges: [
        { value: 'all', label: 'All time' },
        { value: 'today', label: 'Today' },
        { value: 'week', label: 'This week' },
        { value: 'custom', label: 'Custom...' }
      ]
    };
  },
  methods: {
    selectStatus(status, event) {
      this.$emit('update-status', status);
      // Add a subtle animation effect
      const button = event.currentTarget;
      button.classList.add('btn-pulse');
      setTimeout(() => button.classList.remove('btn-pulse'), 300);
    },
    
    toggleTimeFilter() {
      this.showTimeFilterPopup = !this.showTimeFilterPopup;
      // Close popup when clicking outside
      if (this.showTimeFilterPopup) {
        setTimeout(() => {
          document.addEventListener('click', this.closeTimeFilterPopup);
        }, 100);
      }
    },
    
    closeTimeFilterPopup(event) {
      // Check if clicked outside popup and toggle
      if (!event.target.closest('.time-filter-popup') && 
          !event.target.closest('.time-filter-toggle')) {
        this.showTimeFilterPopup = false;
        document.removeEventListener('click', this.closeTimeFilterPopup);
      }
    },
    
    selectTimeRange(value, label) {
      this.selectedTimeRange = label;
      this.timeFilterActive = value !== 'all';
      this.showTimeFilterPopup = false;
      this.$emit('update-time-filter', value);
      
      document.removeEventListener('click', this.closeTimeFilterPopup);
    }
  },
  // Clean up event listener when component is destroyed
  beforeDestroy() {
    document.removeEventListener('click', this.closeTimeFilterPopup);
  }
}
</script>

<style scoped>
.task-filter {
  margin-bottom: 1.25rem;
  position: relative; /* For positioning of popup */
}

.filters-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.status-filter {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.status-btn {
  padding: 0.3rem 0.6rem;
  background-color: transparent;
  border: 1px solid #333;
  color: #999;
  border-radius: 20px;
  cursor: pointer;
  font-size: 0.7rem;
  transition: all 0.2s ease;
}

.status-btn:hover {
  border-color: #555;
  color: #ddd;
}

.status-btn.active {
  background-color: #333;
  color: #fff;
  border-color: #333;
}

/* Time filter toggle button */
.time-filter-toggle {
  background-color: transparent;
  border: 1px solid #333;
  color: #999;
  border-radius: 20px;
  cursor: pointer;
  font-size: 0.7rem;
  padding: 0.3rem 0.6rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.2s ease;
}

.time-filter-toggle:hover {
  border-color: #555;
  color: #ddd;
}

.time-filter-toggle.active {
  background-color: #333;
  color: #fff;
  border-color: #333;
}

/* Time filter popup */
.time-filter-popup {
  position: absolute;
  right: 0;
  top: calc(100% + 0.5rem);
  background-color: #222;
  border-radius: 6px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
  padding: 0.5rem;
  z-index: 101;
  min-width: 150px;
  border: 1px solid #333;
  animation: fadeIn 0.2s ease-out;
}

.time-option {
  padding: 0.6rem 0.8rem;
  color: #ddd;
  font-size: 0.75rem;
  cursor: pointer;
  border-radius: 4px;
  transition: background-color 0.15s ease;
}

.time-option:hover {
  background-color: #333;
}

.time-option.active {
  color: #8a56ff;
  background-color: rgba(138, 86, 255, 0.1);
}

/* Animation */
@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.05); }
  100% { transform: scale(1); }
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-5px); }
  to { opacity: 1; transform: translateY(0); }
}

.btn-pulse {
  animation: pulse 0.3s ease-in-out;
}
</style>