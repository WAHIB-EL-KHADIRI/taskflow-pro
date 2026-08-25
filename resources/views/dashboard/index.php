<?php

/**
 * Dashboard overview.
 *
 * Everything here comes from GetDashboardDataUseCase. Note the column names
 * differ by table on purpose: projects, teams and workspaces carry `name`,
 * tasks carry `title`. They are not interchangeable.
 */

use App\Http\Security;

$stats = is_array($stats ?? null) ? $stats : [];
$projects = is_array($projects ?? null) ? $projects : [];
$tasks = is_array($tasks ?? null) ? $tasks : [];
$teams = is_array($teams ?? null) ? $teams : [];
$workspaces = is_array($workspaces ?? null) ? $workspaces : [];
?>
<h1>Tableau de bord</h1>

<section class="stats" aria-label="Chiffres cles">
    <?php
    $cards = [
        'Projets' => $stats['totalProjects'] ?? 0,
        'Taches' => $stats['totalTasks'] ?? 0,
        'Terminees' => $stats['completedTasks'] ?? 0,
        'En retard' => $stats['overdueTasks'] ?? 0,
        'Equipes' => $stats['totalTeams'] ?? 0,
    ];
    ?>
    <?php foreach ($cards as $label => $value) : ?>
        <div class="stat-card">
            <span class="stat-value"><?= Security::escape((string) $value) ?></span>
            <span class="stat-label"><?= Security::escape($label) ?></span>
        </div>
    <?php endforeach; ?>
</section>

<section aria-labelledby="recent-tasks">
    <h2 id="recent-tasks">Taches recentes</h2>

    <?php if ($tasks === []) : ?>
        <p class="empty">Aucune tache pour le moment.</p>
    <?php else : ?>
        <table class="table">
            <thead>
            <tr>
                <th scope="col">Tache</th>
                <th scope="col">Projet</th>
                <th scope="col">Statut</th>
                <th scope="col">Echeance</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($tasks as $task) : ?>
                <tr>
                    <td>
                        <a href="/tasks/<?= Security::escape((string) ($task['id'] ?? '')) ?>">
                            <?= Security::escape((string) ($task['title'] ?? '')) ?>
                        </a>
                    </td>
                    <td><?= Security::escape((string) ($task['project_name'] ?? '')) ?></td>
                    <td><?= Security::escape((string) ($task['status'] ?? '')) ?></td>
                    <td><?= Security::escape((string) ($task['due_date'] ?? '')) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<section aria-labelledby="my-projects">
    <h2 id="my-projects">Projets</h2>

    <?php if ($projects === []) : ?>
        <p class="empty">Aucun projet.</p>
    <?php else : ?>
        <ul class="card-list">
            <?php foreach ($projects as $project) : ?>
                <li>
                    <a href="/projects/<?= Security::escape((string) ($project['id'] ?? '')) ?>">
                        <?= Security::escape((string) ($project['name'] ?? '')) ?>
                    </a>
                    <span class="badge"><?= Security::escape((string) ($project['status'] ?? '')) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>

<section aria-labelledby="my-teams">
    <h2 id="my-teams">Equipes et espaces</h2>

    <?php if ($teams === [] && $workspaces === []) : ?>
        <p class="empty">Aucune equipe ni espace de travail.</p>
    <?php else : ?>
        <ul class="card-list">
            <?php foreach ($workspaces as $workspace) : ?>
                <li>
                    <a href="/workspaces/<?= Security::escape((string) ($workspace['id'] ?? '')) ?>">
                        <?= Security::escape((string) ($workspace['name'] ?? '')) ?>
                    </a>
                    <span class="badge">espace</span>
                </li>
            <?php endforeach; ?>
            <?php foreach ($teams as $team) : ?>
                <li>
                    <a href="/teams/<?= Security::escape((string) ($team['id'] ?? '')) ?>">
                        <?= Security::escape((string) ($team['name'] ?? '')) ?>
                    </a>
                    <span class="badge"><?= Security::escape((string) ($team['role'] ?? 'membre')) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
