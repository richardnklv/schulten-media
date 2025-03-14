<!-- resources/js/components/ResizableSplitter.vue -->
<template>
    <div 
      class="splitter"
      @mousedown="startResize"
      @touchstart="startResize"
    >
      <div class="splitter-handle"></div>
    </div>
  </template>
  
  <script>
  export default {
    name: 'ResizableSplitter',
    emits: ['resize'],
    methods: {
      startResize(event) {
        event.preventDefault();
        
        // Determine if it's a mouse or touch event
        const clientX = event.touches ? event.touches[0].clientX : event.clientX;
        
        // Store the initial position
        this.startX = clientX;
        this.isResizing = true;
        
        // Add event listeners for mouse/touch move and up
        if (event.touches) {
          document.addEventListener('touchmove', this.resize);
          document.addEventListener('touchend', this.stopResize);
        } else {
          document.addEventListener('mousemove', this.resize);
          document.addEventListener('mouseup', this.stopResize);
        }
        
        // Add resize class to body to prevent text selection during resize
        document.body.classList.add('resizing');
      },
      
      resize(event) {
        if (!this.isResizing) return;
        
        // Get the current position
        const clientX = event.touches ? event.touches[0].clientX : event.clientX;
        
        // Calculate the movement
        const deltaX = clientX - this.startX;
        
        // Emit the resize event with the delta
        this.$emit('resize', deltaX);
        
        // Update the start position for the next move
        this.startX = clientX;
      },
      
      stopResize() {
        this.isResizing = false;
        
        // Remove event listeners
        document.removeEventListener('mousemove', this.resize);
        document.removeEventListener('mouseup', this.stopResize);
        document.removeEventListener('touchmove', this.resize);
        document.removeEventListener('touchend', this.stopResize);
        
        // Remove resize class from body
        document.body.classList.remove('resizing');
      }
    },
    data() {
      return {
        startX: 0,
        isResizing: false
      };
    },
    beforeUnmount() {
      // Clean up any lingering event listeners
      this.stopResize();
    }
  };
  </script>
  
  <style scoped>
  .splitter {
    width: 8px;
    margin: 0 -4px; /* Center the splitter on the border */
    background-color: transparent;
    cursor: col-resize;
    position: relative;
    z-index: 10;
    touch-action: none;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
  }
  
  .splitter-handle {
    width: 2px;
    height: 40px;
    background-color: #333;
    border-radius: 1px;
    transition: background-color 0.2s ease, height 0.2s ease;
  }
  
  .splitter:hover .splitter-handle {
    background-color: #666;
    height: 50px;
  }
  
  /* Add a global style for when resizing is active */
  :global(body.resizing) {
    cursor: col-resize;
    user-select: none;
  }
  
  :global(body.resizing .splitter-handle) {
    background-color: #8a56ff !important;
    height: 60px !important;
  }
  </style>