<!-- resources/js/components/task-panel/SearchContainer.vue -->
<template>
  <div class="search-container">
    <div class="search-input-wrapper">
      <input 
        type="text" 
        v-model="searchQueryModel" 
        placeholder="Filter tasks..." 
        class="zen-search-input"
        @input="handleSearch"
      />
      <button v-if="searchQueryModel" @click="clearSearch" class="clear-search-btn">
        <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2" fill="none">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>
      <svg v-else class="search-icon" viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2" fill="none">
        <circle cx="11" cy="11" r="8"></circle>
        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
      </svg>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SearchContainer',
  props: {
    searchQuery: {
      type: String,
      default: ''
    }
  },
  computed: {
    searchQueryModel: {
      get() {
        return this.searchQuery;
      },
      set(value) {
        this.$emit('update:searchQuery', value);
      }
    }
  },
  methods: {
    handleSearch() {
      // Debounce logic could be added here if needed
      this.$emit('search');
    },
    clearSearch() {
      this.searchQueryModel = '';
      // Add a subtle animation effect
      const searchInput = document.querySelector('.zen-search-input');
      searchInput.classList.add('cleared');
      setTimeout(() => searchInput.classList.remove('cleared'), 300);
      this.$emit('search');
    }
  }
}
</script>

<style scoped>
.search-container {
  margin-bottom: 0.75rem;
  display: flex;
  justify-content: center;
  width: 100%;
}

.search-input-wrapper {
  position: relative;
  width: 100%;
}

.zen-search-input {
  width: 100%;
  padding: 0.5rem 2.2rem 0.5rem 2.2rem;
  background-color: rgba(40, 40, 40, 0.5);
  border: 1px solid #2c2c2c;
  border-radius: 6px;
  color: #eee;
  font-size: 0.8rem;
  transition: all 0.3s ease;
  letter-spacing: 0.3px;
}

.zen-search-input:focus {
  outline: none;
  background-color: rgba(42, 42, 42, 0.6);
  border-color: #333;
}

.zen-search-input::placeholder {
  color: #666;
  font-style: italic;
  font-size: 0.75rem;
  letter-spacing: 0.5px;
}

.search-icon {
  position: absolute;
  left: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  color: #555;
  opacity: 0.7;
  pointer-events: none;
}

.clear-search-btn {
  position: absolute;
  right: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #555;
  opacity: 0.7;
  cursor: pointer;
  transition: all 0.2s ease;
}

.clear-search-btn:hover {
  color: #999;
}

.zen-search-input.cleared {
  animation: clearSubtle 0.4s ease;
}

@keyframes clearSubtle {
  0% {
    opacity: 1;
  }
  50% {
    opacity: 0.8;
  }
  100% {
    opacity: 1;
  }
}
</style>