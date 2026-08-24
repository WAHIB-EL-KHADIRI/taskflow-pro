<?php

/**
 * Kanban board.
 *
 * The controller has already bucketed the tasks, and it owns the list of
 * columns. This file renders whatever buckets it is handed, so adding a status
 * is a controller change and cannot be half-done by editing only the markup.
 */

use App\Http\Security;

$board = is_array($board ?? null) ? $board : [];

$columnLabels = [
    'todo' => 'A faire',
    'in_progress' => 'En cours',
    'done' => 'Termine',
];
?>
<h1>Kanban</h1>

<div class="kanban">
    <?php foreach ($board as $status => $columnTasks) : ?>
        <?php $label = $columnLabels[$status] ?? (string) $status; ?>
        <section class="kanban-column" aria-labelledby="col-<?= Security::escape((string) $status) ?>">
            <h2 id="col-<?= Security::escape((string) $status) ?>">
                <?= Security::escape($label) ?>
                <span class="count"><?= Security::escape((string) count($columnTasks)) ?></span>
            </h2>

            <?php if ($columnTasks === []) : ?>
                <p class="empty">Vide.</p>
            <?php else : ?>
                <ul class="kanban-cards">
                    <?php foreach ($columnTasks as $task) : ?>
                        <li class="kanban-card">
                            <a href="/tasks/<?= Security::escape((string) ($task['id'] ?? '')) ?>">
                                <?= Security::escape((string) ($task['title'] ?? '')) ?>
                            </a>
                            <?php if (($task['priority'] ?? 'none') !== 'none') : ?>
                                <span class="badge priority-<?= Security::escape((string) $task['priority']) ?>">
                                    <?= Security::escape((string) $task['priority']) ?>
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($task['due_date'])) : ?>
                                <time datetime="<?= Security::escape((string) $task['due_date']) ?>">
                                    <?= Security::escape((string) $task['due_date']) ?>
                                </time>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    <?php endforeach; ?>
</div>
