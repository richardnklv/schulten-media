import { describe, it, expect, beforeEach, vi } from 'vitest';
import { setActivePinia, createPinia } from 'pinia';
import { useTaskStore } from '../../resources/js/stores/taskStore';
import taskService from '../../resources/js/services/taskService';

// Mock the taskService
vi.mock('../../resources/js/services/taskService', () => ({
  default: {
    getTasks: vi.fn(),
    getTask: vi.fn(),
    createTask: vi.fn(),
    updateTask: vi.fn(),
    deleteTask: vi.fn()
  }
}));

describe('taskStore', () => {
  let store;

  beforeEach(() => {
    setActivePinia(createPinia());
    store = useTaskStore();
    
    // Reset mocks
    vi.clearAllMocks();
  });

  it('loads tasks from API', async () => {
    const mockTasks = [
      { id: 1, title: 'Task 1', priority: 'high', status: 'todo' },
      { id: 2, title: 'Task 2', priority: 'medium', status: 'in_progress' }
    ];
    
    taskService.getTasks.mockResolvedValue(mockTasks);
    
    await store.loadTasks(1);
    
    expect(taskService.getTasks).toHaveBeenCalledWith(1);
    expect(store.tasks).toEqual(mockTasks);
    expect(store.isLoading).toBe(false);
  });

  it('adds a new task', async () => {
    const newTask = { title: 'New Task', priority: 'medium', status: 'todo', project_id: 1 };
    const createdTask = { id: 3, ...newTask };
    
    taskService.createTask.mockResolvedValue(createdTask);
    
    await store.addTask(newTask);
    
    expect(taskService.createTask).toHaveBeenCalledWith(newTask);
    expect(store.tasks).toContainEqual(createdTask);
  });

  it('updates a task', async () => {
    // Setup initial state
    store.tasks = [
      { id: 1, title: 'Task 1', priority: 'high', status: 'todo' }
    ];
    
    const updatedTask = { id: 1, title: 'Updated Task', priority: 'high', status: 'todo' };
    
    taskService.updateTask.mockResolvedValue(updatedTask);
    
    await store.updateTask(updatedTask);
    
    expect(taskService.updateTask).toHaveBeenCalledWith(1, updatedTask);
    expect(store.tasks[0].title).toBe('Updated Task');
  });

  it('updates task status', async () => {
    // Setup initial state
    store.tasks = [
      { id: 1, title: 'Task 1', priority: 'high', status: 'todo' }
    ];
    
    const updatedTask = { id: 1, title: 'Task 1', priority: 'high', status: 'in_progress' };
    
    taskService.updateTask.mockResolvedValue(updatedTask);
    
    await store.updateTaskStatus(1, 'in_progress');
    
    expect(taskService.updateTask).toHaveBeenCalledWith(1, { status: 'in_progress' });
    expect(store.tasks[0].status).toBe('in_progress');
  });

  it('filters tasks correctly', () => {
    // Setup initial state
    store.tasks = [
      { id: 1, title: 'Task 1', priority: 'high', status: 'todo' },
      { id: 2, title: 'Task 2', priority: 'medium', status: 'in_progress' },
      { id: 3, title: 'Another task', priority: 'low', status: 'completed' }
    ];
    
    // Test priority filter
    store.filters.priority = 'high';
    expect(store.filteredTasks).toEqual([
      { id: 1, title: 'Task 1', priority: 'high', status: 'todo' }
    ]);
    
    // Test status filter
    store.filters.priority = null;
    store.filters.status = 'in_progress';
    expect(store.filteredTasks).toEqual([
      { id: 2, title: 'Task 2', priority: 'medium', status: 'in_progress' }
    ]);
    
    // Test search filter
    store.filters.status = null;
    store.searchQuery = 'Another';
    expect(store.filteredTasks).toEqual([
      { id: 3, title: 'Another task', priority: 'low', status: 'completed' }
    ]);
  });
});