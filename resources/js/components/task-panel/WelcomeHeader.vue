<!-- resources/js/components/task-panel/WelcomeHeader.vue -->
<template>
  <div class="welcome-header">
    <h2>Hey {{ userName }}!</h2>
    <p class="subtitle">Focus on what matters most</p>
  </div>
</template>

<script>
export default {
  name: 'WelcomeHeader',
  props: {
    userName: {
      type: String,
      default: 'there'
    }
  }
}
</script>

<style scoped>
.welcome-header {
  margin-bottom: 1rem;
  text-align: center;
}

.welcome-header h2 {
  font-size: 1.5rem;
  font-weight: 500;
  margin-bottom: 0.25rem;
  letter-spacing: 0.5px;
}

.subtitle {
  color: #999;
  font-size: 0.85rem;
  letter-spacing: 0.5px;
}
</style>