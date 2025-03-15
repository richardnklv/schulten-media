import { describe, it, expect, beforeEach, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import TaskList from '../../resources/js/components/task-panel/TaskList.vue';
import { useTaskStore } from '../../resources/js/stores/taskStore';

// Mock the taskService
vi.mock('../../resources/js/services/taskService', () => ({
  default: {
    getTasks: vi.fn().mockResolvedValue([
      { id: 1, title: 'Task 1', priority: 'high', status: 'todo' },
      { id: 2, title: 'Task 2', priority: 'medium', status: 'in_progress' },
      { id: 3, title: 'Task 3', priority: 'low', status: 'completed' }
    ]),
    updateTask: vi.fn().mockResolvedValue({ success: true })
  }
}));

describe('TaskList.vue', () => {
  let wrapper;
  let taskStore;

  beforeEach(() => {
    // Create a fresh Pinia instance for each test
    setActivePinia(createPinia());
    taskStore = useTaskStore();
    
    // Initialize store with some test data
    taskStore.tasks = [
      { id: 1, title: 'Task 1', priority: 'high', status: 'todo' },
      { id: 2, title: 'Task 2', priority: 'medium', status: 'in_progress' },
      { id: 3, title: 'Task 3', priority: 'low', status: 'completed' }
    ];
    
    wrapper = mount(TaskList, {
      global: {
        plugins: [createPinia()],
        stubs: ['RouterLink']
      },
      props: {
        projectId: 1
      }
    });
  });

  it('renders the correct number of tasks', () => {
    const taskItems = wrapper.findAll('.task-item');
    expect(taskItems.length).toBe(3);
  });

  it('filters tasks by priority when filter is applied', async () => {
    // Set filter to 'high'
    await taskStore.updateFilter('priority', 'high');
    await wrapper.vm.$nextTick();
    
    // Should only show high priority tasks
    const taskItems = wrapper.findAll('.task-item');
    expect(taskItems.length).toBe(1);
    expect(taskItems[0].text()).toContain('Task 1');
  });

  it('updates task status when drag and drop occurs', async () => {
    // Mock the drop event
    const mockDropEvent = {
      dataTransfer: {
        getData: () => JSON.stringify({ id: 1, status: 'todo' })
      },
      preventDefault: vi.fn()
    };
    
    // Call the handleDrop method directly with the target status
    await wrapper.vm.handleDrop(mockDropEvent, 'in_progress');
    
    // Verify the task's status was updated in the store
    expect(taskStore.updateTaskStatus).toHaveBeenCalledWith(1, 'in_progress');
  });

  it('shows empty state when no tasks match filter', async () => {
    // Set filter to a value that won't match any tasks
    await taskStore.updateFilter('status', 'non_existent');
    await wrapper.vm.$nextTick();
    
    // Should show empty state
    expect(wrapper.find('.empty-tasks').exists()).toBe(true);
  });

  it('loads tasks when projectId changes', async () => {
    // Change the projectId prop
    await wrapper.setProps({ projectId: 2 });
    
    // Verify loadTasks was called with the new projectId
    expect(taskStore.loadTasks).toHaveBeenCalledWith(2);
  });
});