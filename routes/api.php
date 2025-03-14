<?php
// File: routes/api.php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\TaskController;
use App\Http\Controllers\API\ProjectController;
use App\Http\Controllers\API\CommentController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;


Route::get('/test', function () {
    return ['message' => 'API is working!'];
});

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // User routes
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/users', [UserController::class, 'index']);
    
    // Project routes
    Route::apiResource('projects', ProjectController::class);
    
    // Task routes
    Route::apiResource('tasks', TaskController::class);
    Route::put('/tasks/{task}/priority', [TaskController::class, 'updatePriority']);
    
    // Comment routes
    Route::get('/tasks/{task}/comments', [CommentController::class, 'index']);
    Route::post('/tasks/{task}/comments', [CommentController::class, 'store']);
    Route::put('/comments/{comment}', [CommentController::class, 'update']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
    
    Route::post('/logout', [AuthController::class, 'logout']);
});