<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

$refresh = in_array('refresh', $argv ?? []) || in_array('fresh', $argv ?? []);
$seedData = in_array('seed', $argv ?? []) || in_array('refresh', $argv ?? []);

$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$port = $_ENV['DB_PORT'] ?? '3306';
$name = $_ENV['DB_DATABASE'] ?? 'taskflow_pro';
$user = $_ENV['DB_USERNAME'] ?? 'root';
$pass = $_ENV['DB_PASSWORD'] ?? '';

try {
    $pdo = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$name}`");

    if ($refresh) {
        echo "Dropping all tables...\n";
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tables as $t) {
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
            $pdo->exec("DROP TABLE IF EXISTS `{$t}`");
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        }
        echo "Done.\n";
    }

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    // Users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(150) NOT NULL,
        `email` VARCHAR(200) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL,
        `role` ENUM('super_admin','admin','user') NOT NULL DEFAULT 'user',
        `avatar` VARCHAR(500) NULL,
        `bio` TEXT NULL,
        `theme` ENUM('light','dark') NOT NULL DEFAULT 'light',
        `locale` VARCHAR(5) NOT NULL DEFAULT 'fr',
        `email_verified` TINYINT(1) NOT NULL DEFAULT 0,
        `remember_token` VARCHAR(500) NULL,
        `reset_token` VARCHAR(500) NULL,
        `reset_token_expires` DATETIME NULL,
        `last_login` DATETIME NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX `idx_users_email` (`email`),
        INDEX `idx_users_role` (`role`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Workspaces table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `workspaces` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(150) NOT NULL,
        `slug` VARCHAR(200) NOT NULL UNIQUE,
        `description` TEXT NULL,
        `logo` VARCHAR(500) NULL,
        `owner_id` INT UNSIGNED NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX `idx_workspaces_owner` (`owner_id`),
        INDEX `idx_workspaces_slug` (`slug`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Workspace members
    $pdo->exec("CREATE TABLE IF NOT EXISTS `workspace_members` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `workspace_id` INT UNSIGNED NOT NULL,
        `user_id` INT UNSIGNED NOT NULL,
        `role` ENUM('owner','admin','member') NOT NULL DEFAULT 'member',
        `joined_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY `uk_workspace_member` (`workspace_id`,`user_id`),
        INDEX `idx_wm_workspace` (`workspace_id`),
        INDEX `idx_wm_user` (`user_id`),
        CONSTRAINT `fk_wm_workspace` FOREIGN KEY (`workspace_id`) REFERENCES `workspaces` (`id`) ON DELETE CASCADE,
        CONSTRAINT `fk_wm_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Teams table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `teams` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(150) NOT NULL,
        `description` TEXT NULL,
        `color` VARCHAR(7) DEFAULT '#6366f1',
        `workspace_id` INT UNSIGNED NOT NULL,
        `owner_id` INT UNSIGNED NOT NULL,
        `is_default` TINYINT(1) DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX `idx_teams_workspace` (`workspace_id`),
        CONSTRAINT `fk_teams_workspace` FOREIGN KEY (`workspace_id`) REFERENCES `workspaces` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Team members
    $pdo->exec("CREATE TABLE IF NOT EXISTS `team_members` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `team_id` INT UNSIGNED NOT NULL,
        `user_id` INT UNSIGNED NOT NULL,
        `role` ENUM('lead','member') NOT NULL DEFAULT 'member',
        `joined_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY `uk_team_member` (`team_id`,`user_id`),
        INDEX `idx_tm_user` (`user_id`),
        INDEX `idx_tm_team` (`team_id`),
        CONSTRAINT `fk_tm_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
        CONSTRAINT `fk_tm_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Projects table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `projects` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(200) NOT NULL,
        `description` TEXT NULL,
        `color` VARCHAR(7) DEFAULT '#4f46e5',
        `status` ENUM('active','archived','completed') NOT NULL DEFAULT 'active',
        `workspace_id` INT UNSIGNED NOT NULL,
        `team_id` INT UNSIGNED NULL,
        `created_by` INT UNSIGNED NOT NULL,
        `start_date` DATE NULL,
        `end_date` DATE NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX `idx_projects_workspace` (`workspace_id`),
        INDEX `idx_projects_team` (`team_id`),
        INDEX `idx_projects_created_by` (`created_by`),
        INDEX `idx_projects_status` (`status`),
        CONSTRAINT `fk_proj_workspace` FOREIGN KEY (`workspace_id`) REFERENCES `workspaces` (`id`) ON DELETE CASCADE,
        CONSTRAINT `fk_proj_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL,
        CONSTRAINT `fk_proj_owner` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Project members
    $pdo->exec("CREATE TABLE IF NOT EXISTS `project_members` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `project_id` INT UNSIGNED NOT NULL,
        `user_id` INT UNSIGNED NOT NULL,
        `role` ENUM('manager','member','viewer') NOT NULL DEFAULT 'member',
        `joined_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY `uk_project_member` (`project_id`,`user_id`),
        INDEX `idx_pm_user` (`user_id`),
        INDEX `idx_pm_project` (`project_id`),
        CONSTRAINT `fk_pm_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
        CONSTRAINT `fk_pm_member` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Tasks table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `tasks` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `title` VARCHAR(300) NOT NULL,
        `description` TEXT NULL,
        `status` ENUM('todo','in_progress','done') NOT NULL DEFAULT 'todo',
        `priority` ENUM('none','low','medium','high','urgent') NOT NULL DEFAULT 'medium',
        `due_date` DATE NULL,
        `finished_at` TIMESTAMP NULL,
        `position` INT DEFAULT 0,
        `project_id` INT UNSIGNED NOT NULL,
        `assigned_to` INT UNSIGNED NULL,
        `created_by` INT UNSIGNED NOT NULL,
        `repeat_interval` VARCHAR(50) NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX `idx_tasks_project` (`project_id`),
        INDEX `idx_tasks_assigned` (`assigned_to`),
        INDEX `idx_tasks_status` (`status`),
        INDEX `idx_tasks_priority` (`priority`),
        INDEX `idx_tasks_duedate` (`due_date`),
        INDEX `idx_tasks_proj_status` (`project_id`,`status`),
        INDEX `idx_tasks_proj_position` (`project_id`,`position`),
        CONSTRAINT `fk_tasks_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
        CONSTRAINT `fk_tasks_assigned` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
        CONSTRAINT `fk_tasks_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Sub-tasks table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `sub_tasks` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `title` VARCHAR(300) NOT NULL,
        `completed` TINYINT(1) DEFAULT 0,
        `task_id` INT UNSIGNED NOT NULL,
        `assigned_to` INT UNSIGNED NULL,
        `position` INT DEFAULT 0,
        `completed_at` TIMESTAMP NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX `idx_sub_tasks_task` (`task_id`),
        INDEX `idx_sub_tasks_position` (`task_id`,`position`),
        CONSTRAINT `fk_sub_tasks_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Tags table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `tags` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL,
        `color` VARCHAR(7) DEFAULT '#6b7280',
        `workspace_id` INT UNSIGNED NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY `uk_tag_workspace` (`name`,`workspace_id`),
        INDEX `idx_tags_workspace` (`workspace_id`),
        CONSTRAINT `fk_tags_workspace` FOREIGN KEY (`workspace_id`) REFERENCES `workspaces` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Task-Tag pivot
    $pdo->exec("CREATE TABLE IF NOT EXISTS `task_tags` (
        `task_id` INT UNSIGNED NOT NULL,
        `tag_id` INT UNSIGNED NOT NULL,
        PRIMARY KEY (`task_id`,`tag_id`),
        CONSTRAINT `fk_tt_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
        CONSTRAINT `fk_tt_tag` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Comments table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `comments` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `content` TEXT NOT NULL,
        `task_id` INT UNSIGNED NOT NULL,
        `user_id` INT UNSIGNED NOT NULL,
        `parent_id` INT UNSIGNED NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX `idx_comments_task` (`task_id`),
        INDEX `idx_comments_user` (`user_id`),
        INDEX `idx_comments_parent` (`parent_id`),
        CONSTRAINT `fk_comments_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
        CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
        CONSTRAINT `fk_comments_parent` FOREIGN KEY (`parent_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Task attachments
    $pdo->exec("CREATE TABLE IF NOT EXISTS `task_attachments` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `task_id` INT UNSIGNED NOT NULL,
        `user_id` INT UNSIGNED NOT NULL,
        `filename` VARCHAR(255) NOT NULL,
        `original_name` VARCHAR(255) NOT NULL,
        `file_size` INT UNSIGNED NOT NULL,
        `mime_type` VARCHAR(100) NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_attachments_task` (`task_id`),
        CONSTRAINT `fk_attachments_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
        CONSTRAINT `fk_attachments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Notifications table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `notifications` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT UNSIGNED NOT NULL,
        `type` VARCHAR(100) NOT NULL,
        `title` VARCHAR(300) NOT NULL,
        `body` TEXT NULL,
        `link` VARCHAR(500) NULL,
        `is_read` TINYINT(1) DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_notif_user` (`user_id`),
        INDEX `idx_notif_read` (`user_id`,`is_read`),
        CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Activity log
    $pdo->exec("CREATE TABLE IF NOT EXISTS `activity_log` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT UNSIGNED NOT NULL,
        `action` VARCHAR(100) NOT NULL,
        `entity_type` VARCHAR(50) NOT NULL,
        `entity_id` INT UNSIGNED NULL,
        `workspace_id` INT UNSIGNED NULL,
        `project_id` INT UNSIGNED NULL,
        `task_id` INT UNSIGNED NULL,
        `description` TEXT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_activity_user` (`user_id`),
        INDEX `idx_activity_entity` (`entity_type`,`entity_id`),
        INDEX `idx_activity_workspace` (`workspace_id`),
        INDEX `idx_activity_project` (`project_id`),
        INDEX `idx_activity_task` (`task_id`),
        INDEX `idx_activity_created` (`created_at`),
        CONSTRAINT `fk_activity_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Task dependencies
    $pdo->exec("CREATE TABLE IF NOT EXISTS `task_dependencies` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `task_id` INT UNSIGNED NOT NULL,
        `depends_on_id` INT UNSIGNED NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY `uk_task_dep` (`task_id`,`depends_on_id`),
        CONSTRAINT `fk_td_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
        CONSTRAINT `fk_td_depends` FOREIGN KEY (`depends_on_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    echo "All tables created successfully!\n";

    if ($seedData) {
        $adminPassword = password_hash('admin123', PASSWORD_BCRYPT, ['cost' => 12]);
        $userPassword = password_hash('user123', PASSWORD_BCRYPT, ['cost' => 12]);

        $pdo->exec("INSERT INTO `users` (`name`, `email`, `password`, `role`, `locale`, `theme`, `email_verified`)
            VALUES
            ('Admin Test', 'admin@test.com', '{$adminPassword}', 'super_admin', 'fr', 'light', 1),
            ('User Test', 'user@test.com', '{$userPassword}', 'user', 'fr', 'dark', 1)");

        echo "Seed data inserted.\n";
    }

    echo "\n=== Migration completed successfully! ===\n";

} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage() . "\n");
}
