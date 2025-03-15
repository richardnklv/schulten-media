<template>
  <div class="sidebar-section">
    <h4 class="section-title" v-if="!isCollapsed">Team</h4>
    <ul class="team-list">
      <li v-for="member in team" :key="member.id" class="team-item">
        <div class="team-avatar">
          <span>{{ getUserInitials(member.name) }}</span>
        </div>
        <span class="team-name" v-if="!isCollapsed">{{ member.name }}</span>
        <span class="status-dot" :class="getRandomStatus()"></span>
      </li>
    </ul>
  </div>
</template>

<script>
export default {
  name: 'TeamList',
  props: {
    team: {
      type: Array,
      required: true
    },
    isCollapsed: {
      type: Boolean,
      required: true
    }
  },
  methods: {
    getUserInitials(name) {
      if (!name) return '';
      return name.split(' ').map(n => n[0]).join('');
    },
    getRandomStatus() {
      // For demo purposes, assign random status to team members
      const statuses = ['online', 'offline', 'away'];
      return statuses[Math.floor(Math.random() * statuses.length)];
    }
  }
};
</script>

<style scoped>
.sidebar-section {
  margin-bottom: 2rem;
  width: 100%;
}

:deep(.project-sidebar.collapsed) .sidebar-section {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.section-title {
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: #777;
  margin-bottom: 0.8rem;
  font-weight: 500;
  display: flex;
  align-items: center;
}

.team-list {
  list-style: none;
  padding: 0;
  margin: 0;
  width: 100%;
}

:deep(.project-sidebar.collapsed) .team-list {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.team-item {
  display: flex;
  align-items: center;
  padding: 0.5rem;
  font-size: 0.85rem;
  border-radius: 4px;
  transition: all 0.2s ease;
  position: relative;
  justify-content: space-between;
}

.team-item:hover {
  background-color: rgba(255, 255, 255, 0.05);
}

:deep(.project-sidebar.collapsed) .team-item:hover .team-avatar {
  transform: scale(1.1);
  box-shadow: 0 0 10px rgba(138, 86, 255, 0.3);
}

.team-avatar {
  width: 24px;
  height: 24px;
  min-width: 24px;
  border-radius: 50%;
  background-color: #2a2a2a;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.7rem;
  color: #ddd;
  margin-right: 0.75rem;
  transition: all 0.3s ease;
}

:deep(.project-sidebar.collapsed) .team-avatar {
  width: 28px;
  height: 28px;
  min-width: 28px;
  margin-right: 0;
  font-size: 0.8rem;
  background-color: #333;
}

.team-name {
  flex: 1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.status-dot {
  width: 8px;
  height: 8px;
  min-width: 8px;
  border-radius: 50%;
  margin-left: 0.5rem;
  transition: all 0.3s ease;
}

:deep(.project-sidebar.collapsed) .status-dot {
  position: absolute;
  top: 6px;
  right: 10px;
  width: 6px;
  height: 6px;
  margin-left: 0;
}

.status-dot.online {
  background-color: #2ecc71;
}

.status-dot.offline {
  background-color: #999;
}

.status-dot.away {
  background-color: #f39c12;
}
</style>