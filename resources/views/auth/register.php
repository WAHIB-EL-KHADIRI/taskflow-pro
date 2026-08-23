<?php

/**
 * Registration form.
 *
 * `minlength` mirrors the check in AuthController::validateRegistration. It is
 * there to tell the user early, not to enforce anything -- the controller does
 * not trust it, and neither should any future change to this file.
 */

use App\Http\Security;
use App\Http\Session;

$session = Session::getInstance();
$oldName = $session->flash('old_name') ?? '';
$oldEmail = $session->flash('old_email') ?? '';
?>
<section class="auth-card">
    <h1>Creer un compte</h1>

    <form method="post" action="/register" novalidate>
        <input type="hidden" name="csrf_token" value="<?= Security::escape(Security::generateCsrf()) ?>">

        <label for="name">Nom</label>
        <input
            type="text"
            id="name"
            name="name"
            value="<?= Security::escape($oldName) ?>"
            autocomplete="name"
            required
        >

        <label for="email">Email</label>
        <input
            type="email"
            id="email"
            name="email"
            value="<?= Security::escape($oldEmail) ?>"
            autocomplete="username"
            required
        >

        <label for="password">Mot de passe</label>
        <input
            type="password"
            id="password"
            name="password"
            autocomplete="new-password"
            minlength="8"
            required
        >

        <label for="password_confirmation">Confirmer le mot de passe</label>
        <input
            type="password"
            id="password_confirmation"
            name="password_confirmation"
            autocomplete="new-password"
            minlength="8"
            required
        >

        <button type="submit">Creer le compte</button>
    </form>

    <p>Deja inscrit ? <a href="/login">Se connecter</a></p>
</section>
