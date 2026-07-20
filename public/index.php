<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Bootstrap\App;
use App\Http\Session;
use App\Http\Request;
use App\Http\Router;
use App\Http\View;

App::getInstance()->init();

$session = Session::getInstance();

View::share('csrfToken', $session->csrfToken());
View::share('isAuthenticated', $session->isAuthenticated());
View::share('userName', $session->get('user_name'));
View::share('userId', $session->get('user_id'));
View::share('userTheme', $session->get('user_theme', 'light'));
View::share('appName', $_ENV['APP_NAME'] ?? 'TaskFlow Pro');

$router = Router::getInstance();

require_once __DIR__ . '/../routes/web.php';

$request = Request::capture();
$router->dispatch($request->method(), $_SERVER['REQUEST_URI']);
