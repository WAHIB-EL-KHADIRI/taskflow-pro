<?php

declare(strict_types=1);

namespace App\Http\Controllers;

class ApiController extends AbstractController
{
    public function workspaces(): void
    {
        $this->requireAuth();
        $this->json([]);
    }

    public function projects(): void
    {
        $this->requireAuth();
        $this->json([]);
    }

    public function tasks(): void
    {
        $this->requireAuth();
        $this->json([]);
    }

    public function notifications(): void
    {
        $this->requireAuth();
        $this->json([]);
    }

    public function markNotificationsRead(): void
    {
        $this->requireAuth();
        $this->success(message: 'Notifications marked as read');
    }

    public function search(): void
    {
        $this->requireAuth();
        $this->json([]);
    }
}
