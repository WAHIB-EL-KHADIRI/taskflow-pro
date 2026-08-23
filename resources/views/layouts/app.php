<?php

/**
 * Base layout. `View::render()` renders the page first and passes the result
 * in as $slot, so anything printed here wraps an already-rendered page.
 *
 * Nothing in View::renderFile() escapes, so every value printed below goes
 * through Security::escape(). $slot is the exception: it is markup a template
 * produced on purpose, and escaping it would print the HTML as text.
 */

use App\Http\Security;
use App\Http\Session;

$session = Session::getInstance();
$pageTitle = isset($title) && is_string($title) ? $title : 'TaskFlow Pro';
$errorFlash = $session->flash('error');
$successFlash = $session->flash('success');
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= Security::escape($pageTitle) ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<header class="site-header">
    <a class="brand" href="/">TaskFlow Pro</a>
    <nav>
        <?php if ($session->isAuthenticated()) : ?>
            <a href="/dashboard">Tableau de bord</a>
            <a href="/logout">Deconnexion</a>
        <?php else : ?>
            <a href="/login">Connexion</a>
            <a href="/register">Inscription</a>
        <?php endif; ?>
    </nav>
</header>

<main class="site-main">
    <?php if ($errorFlash !== null) : ?>
        <p class="alert alert-error" role="alert"><?= Security::escape($errorFlash) ?></p>
    <?php endif; ?>

    <?php if ($successFlash !== null) : ?>
        <p class="alert alert-success" role="status"><?= Security::escape($successFlash) ?></p>
    <?php endif; ?>

    <?= $slot ?? '' ?>
</main>

<footer class="site-footer">
    <small>TaskFlow Pro</small>
</footer>
</body>
</html>
