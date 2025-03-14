import apiClient from './api';
import notificationService from './notificationService';

export default {
  getForTask(taskId) {
    return apiClient.get(`/tasks/${taskId}/comments`);
  },
  
  async create(taskId, content) {
    const response = await apiClient.post(`/tasks/${taskId}/comments`, { content });
    // Add notification for new comment
    notificationService.addNotification({
      type: 'comment_added',
      title: 'New Comment Added',
      content: `A new comment has been added to task #${taskId}`,
      time: new Date(),
      entityId: taskId,
      entityType: 'task'
    });
    return response;
  },
  
  update(id, content) {
    return apiClient.put(`/comments/${id}`, { content });
  },
  
  delete(id) {
    return apiClient.delete(`/comments/${id}`);
  }
};