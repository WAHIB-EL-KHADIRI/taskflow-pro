<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Http\Session;
use App\Http\View;

abstract class AbstractController
{
    protected Request $request;
    protected Session $session;

    public function __construct()
    {
        $this->request = Request::capture();
        $this->session = Session::getInstance();
    }

    protected function json(array $data, int $status = 200): never
    {
        Response::json($data, $status);
    }

    protected function success(mixed $data = null, string $message = 'Success', int $status = 200): never
    {
        Response::success($data, $message, $status);
    }

    protected function error(string $message = 'Error', int $status = 400, ?array $errors = null): never
    {
        Response::error($message, $status, $errors);
    }

    protected function notFound(string $message = 'Not Found'): never
    {
        Response::notFound($message);
    }

    protected function forbidden(string $message = 'Forbidden'): never
    {
        Response::forbidden($message);
    }

    protected function unauthorized(string $message = 'Unauthorized'): never
    {
        Response::unauthorized($message);
    }

    protected function redirect(string $url, int $status = 302): never
    {
        Response::redirect($url, $status);
    }

    protected function back(): never
    {
        Response::back();
    }

    protected function view(string $view, array $data = [], string $layout = 'app'): void
    {
        View::render($view, $data, $layout);
    }

    protected function userId(): ?int
    {
        return $this->session->userId();
    }

    protected function isAuthenticated(): bool
    {
        return $this->session->isAuthenticated();
    }

    protected function requireAuth(): void
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('/login');
        }
    }

    protected function input(string $key, mixed $default = null): mixed
    {
        return $this->request->input($key, $default);
    }

    protected function only(array $keys): array
    {
        return $this->request->only($keys);
    }
}
