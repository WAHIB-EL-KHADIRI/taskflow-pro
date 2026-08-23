<?php

/**
 * Login form.
 *
 * `old_email` is repopulated but the password never is: putting it back in the
 * markup would leave it in the browser's page cache and in any proxy log.
 */

use App\Http\Security;
use App\Http\Session;

$oldEmail = Session::getInstance()->flash('old_email') ?? '';
?>
<section class="auth-card">
    <h1>Connexion</h1>

    <form method="post" action="/login" novalidate>
        <input type="hidden" name="csrf_token" value="<?= Security::escape(Security::generateCsrf()) ?>">

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
            autocomplete="current-password"
            required
        >

        <button type="submit">Se connecter</button>
    </form>

    <p>Pas encore de compte ? <a href="/register">Creer un compte</a></p>
</section>
