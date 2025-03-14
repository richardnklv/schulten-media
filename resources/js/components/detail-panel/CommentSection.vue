<template>
  <div class="comments-section">
    <h3>Comments</h3>
    <div v-if="comments.length === 0" class="empty-comments">
      No comments yet
    </div>
    <div v-else class="comments-list">
      <div v-for="comment in comments" :key="comment.id" class="comment">
        <div class="comment-header">
          <span class="comment-author">{{ comment.user.name }}</span>
          <span class="comment-date">{{ formatDateTime(comment.created_at) }}</span>
        </div>
        <p class="comment-content">{{ comment.content }}</p>
      </div>
    </div>
    
    <div class="add-comment">
      <textarea 
        v-model="newComment" 
        placeholder="Add a comment..." 
        class="comment-input"
      ></textarea>
      <button class="add-comment-btn" @click="addComment" :disabled="!newComment.trim()">
        Comment
      </button>
    </div>
  </div>
</template>

<script>
import notificationService from '../../services/notificationService';

export default {
  name: 'CommentSection',
  props: {
    comments: {
      type: Array,
      default: () => []
    },
    taskId: {
      type: Number,
      required: true
    },
    taskTitle: {
      type: String,
      default: 'Task'
    }
  },
  data() {
    return {
      newComment: ''
    }
  },
  methods: {
    formatDateTime(dateTimeString) {
      const options = { 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      };
      return new Date(dateTimeString).toLocaleString(undefined, options);
    },
    addComment() {
      if (!this.newComment.trim()) return;
      
      this.$emit('add-comment', this.newComment);
      this.newComment = '';
    }
  }
}
</script>

<style scoped>
/* Comments Section */
.comments-section {
  background-color: #252525;
  padding: 1.25rem;
  border-radius: 6px;
}

.comments-section h3 {
  font-size: 0.9rem;
  margin-bottom: 0.8rem;
  margin-top: 0;
  font-weight: 500;
  letter-spacing: 0.5px;
}

.empty-comments {
  padding: 0.8rem;
  color: #999;
  text-align: center;
  font-style: italic;
  font-size: 0.8rem;
}

.comments-list {
  display: flex;
  flex-direction: column;
  gap: 0.8rem;
  margin-bottom: 1.25rem;
}

.comment {
  background-color: #2a2a2a;
  padding: 0.8rem;
  border-radius: 6px;
}

.comment-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.4rem;
  font-size: 0.7rem;
}

.comment-author {
  font-weight: 500;
}

.comment-date {
  color: #999;
}

.comment-content {
  font-size: 0.8rem;
  line-height: 1.4;
  color: #ccc;
  margin: 0;
}

.add-comment {
  margin-top: 0.8rem;
}

.comment-input {
  width: 100%;
  background-color: #2a2a2a;
  border: 1px solid #333;
  color: #fff;
  padding: 0.6rem;
  border-radius: 6px;
  margin-bottom: 0.6rem;
  resize: vertical;
  height: 80px;
  font-family: inherit;
  font-size: 0.8rem;
}

.add-comment-btn {
  background-color: #8a56ff;
  color: #fff;
  border: none;
  padding: 0.4rem 0.8rem;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.75rem;
  float: right;
  letter-spacing: 0.5px;
}

.add-comment-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>