<?php

declare(strict_types=1);

use App\Http\Router;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ApiController;
use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\CsrfMiddleware;

$router = Router::getInstance();

$router->get('/login', [AuthController::class, 'loginForm']);
$router->post('/login', [AuthController::class, 'login'], [CsrfMiddleware::class]);
$router->get('/register', [AuthController::class, 'registerForm']);
$router->post('/register', [AuthController::class, 'register'], [CsrfMiddleware::class]);
$router->get('/logout', [AuthController::class, 'logout']);

$router->get('/', [DashboardController::class, 'index'], [AuthMiddleware::class]);
$router->get('/dashboard', [DashboardController::class, 'index'], [AuthMiddleware::class]);
$router->get('/kanban', [DashboardController::class, 'kanban'], [AuthMiddleware::class]);
$router->get('/calendar', [DashboardController::class, 'calendar'], [AuthMiddleware::class]);

$router->get('/tasks', [TaskController::class, 'index'], [AuthMiddleware::class]);
$router->get('/tasks/create', [TaskController::class, 'create'], [AuthMiddleware::class]);
$router->post('/tasks/create', [TaskController::class, 'store'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/tasks/{id}', [TaskController::class, 'show'], [AuthMiddleware::class]);
$router->get('/tasks/{id}/edit', [TaskController::class, 'edit'], [AuthMiddleware::class]);
$router->post('/tasks/{id}/edit', [TaskController::class, 'update'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/tasks/{id}/delete', [TaskController::class, 'destroy'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/tasks/{id}/status', [TaskController::class, 'updateStatus'], [AuthMiddleware::class]);
$router->post('/tasks/{id}/position', [TaskController::class, 'updatePosition'], [AuthMiddleware::class]);
$router->post('/tasks/{id}/comment', [TaskController::class, 'addComment'], [AuthMiddleware::class]);
$router->post('/tasks/{id}/subtask', [TaskController::class, 'addSubtask'], [AuthMiddleware::class]);
$router->post('/tasks/{id}/subtask/{sid}/toggle', [TaskController::class, 'toggleSubtask'], [AuthMiddleware::class]);

$router->get('/projects', [ProjectController::class, 'index'], [AuthMiddleware::class]);
$router->get('/projects/create', [ProjectController::class, 'create'], [AuthMiddleware::class]);
$router->post('/projects/create', [ProjectController::class, 'store'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/projects/{id}', [ProjectController::class, 'show'], [AuthMiddleware::class]);
$router->get('/projects/{id}/edit', [ProjectController::class, 'edit'], [AuthMiddleware::class]);
$router->post('/projects/{id}/edit', [ProjectController::class, 'update'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/projects/{id}/delete', [ProjectController::class, 'destroy'], [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/workspaces', [WorkspaceController::class, 'index'], [AuthMiddleware::class]);
$router->get('/workspaces/create', [WorkspaceController::class, 'create'], [AuthMiddleware::class]);
$router->post('/workspaces/create', [WorkspaceController::class, 'store'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/workspaces/{id}', [WorkspaceController::class, 'show'], [AuthMiddleware::class]);
$router->get('/workspaces/{id}/edit', [WorkspaceController::class, 'edit'], [AuthMiddleware::class]);
$router->post('/workspaces/{id}/edit', [WorkspaceController::class, 'update'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/workspaces/{id}/member', [WorkspaceController::class, 'addMember'], [AuthMiddleware::class]);
$router->post('/workspaces/{id}/member/remove', [WorkspaceController::class, 'removeMember'], [AuthMiddleware::class]);

$router->get('/teams', [TeamController::class, 'index'], [AuthMiddleware::class]);
$router->get('/teams/create', [TeamController::class, 'create'], [AuthMiddleware::class]);
$router->post('/teams/create', [TeamController::class, 'store'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/teams/{id}', [TeamController::class, 'show'], [AuthMiddleware::class]);
$router->post('/teams/{id}/member', [TeamController::class, 'addMember'], [AuthMiddleware::class]);

$router->get('/settings', [SettingsController::class, 'index'], [AuthMiddleware::class]);
$router->post('/settings/profile', [SettingsController::class, 'updateProfile'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/settings/password', [SettingsController::class, 'updatePassword'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/settings/theme', [SettingsController::class, 'updateTheme'], [AuthMiddleware::class]);
$router->post('/settings/locale', [SettingsController::class, 'updateLocale'], [AuthMiddleware::class]);

$router->get('/api/workspaces', [ApiController::class, 'workspaces'], [AuthMiddleware::class]);
$router->get('/api/projects', [ApiController::class, 'projects'], [AuthMiddleware::class]);
$router->get('/api/tasks', [ApiController::class, 'tasks'], [AuthMiddleware::class]);
$router->get('/api/notifications', [ApiController::class, 'notifications'], [AuthMiddleware::class]);
$router->post('/api/notifications/read', [ApiController::class, 'markNotificationsRead'], [AuthMiddleware::class]);
$router->get('/api/search', [ApiController::class, 'search'], [AuthMiddleware::class]);
