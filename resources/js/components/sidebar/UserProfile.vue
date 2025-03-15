<template>
  <div class="user-profile">
    <div class="avatar">
      <span v-if="!user.avatar">{{ userInitials }}</span>
      <img v-else :src="user.avatar" :alt="user.name">
    </div>
    <div class="user-info" v-if="!isCollapsed">
      <h3>{{ user.name }}</h3>
      <p class="user-role">Project Manager</p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'UserProfile',
  props: {
    user: {
      type: Object,
      required: true
    },
    isCollapsed: {
      type: Boolean,
      required: true
    }
  },
  computed: {
    userInitials() {
      if (!this.user.name) return '';
      return this.getUserInitials(this.user.name);
    }
  },
  methods: {
    getUserInitials(name) {
      if (!name) return '';
      return name.split(' ').map(n => n[0]).join('');
    }
  }
};
</script>

<style scoped>
.user-profile {
  display: flex;
  align-items: center;
  gap: 0.8rem;
  margin-bottom: 2rem;
  padding-bottom: 1.25rem;
  border-bottom: 1px solid #333;
  transition: all 0.3s ease;
}

:deep(.project-sidebar.collapsed) .user-profile {
  justify-content: center;
  margin-bottom: 1.5rem;
}

.avatar {
  width: 40px;
  height: 40px;
  min-width: 40px;
  border-radius: 50%;
  background-color: #8a56ff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 500;
  color: #fff;
  font-size: 1rem;
  transition: all 0.3s ease;
}

:deep(.project-sidebar.collapsed) .avatar {
  width: 36px;
  height: 36px;
  min-width: 36px;
}

.avatar img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
}

.user-info h3 {
  font-size: 0.9rem;
  font-weight: 500;
  margin: 0;
  margin-bottom: 0.2rem;
}

.user-role {
  font-size: 0.7rem;
  color: #aaa;
  margin: 0;
}
</style>