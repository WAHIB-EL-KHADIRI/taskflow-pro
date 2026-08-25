<?php

/**
 * Calendar view: tasks grouped by due date, earliest first.
 *
 * Tasks with no due date cannot be placed on a date. The controller counts
 * them instead of discarding them, and the count is shown below, so this page
 * never quietly reports fewer tasks than the dashboard does.
 */

use App\Http\Security;

$byDate = is_array($byDate ?? null) ? $byDate : [];
$undated = is_int($undated ?? null) ? $undated : 0;
?>
<h1>Calendrier</h1>

<?php if ($byDate === []) : ?>
    <p class="empty">Aucune tache avec une echeance.</p>
<?php else : ?>
    <?php foreach ($byDate as $date => $dayTasks) : ?>
        <section class="calendar-day">
            <h2>
                <time datetime="<?= Security::escape((string) $date) ?>">
                    <?= Security::escape((string) $date) ?>
                </time>
            </h2>
            <ul class="card-list">
                <?php foreach ($dayTasks as $task) : ?>
                    <li>
                        <a href="/tasks/<?= Security::escape((string) ($task['id'] ?? '')) ?>">
                            <?= Security::escape((string) ($task['title'] ?? '')) ?>
                        </a>
                        <span class="badge"><?= Security::escape((string) ($task['status'] ?? '')) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endforeach; ?>
<?php endif; ?>

<?php if ($undated > 0) : ?>
    <p class="note">
        <?= Security::escape((string) $undated) ?> tache(s) sans echeance ne sont pas affichees ici.
    </p>
<?php endif; ?>
