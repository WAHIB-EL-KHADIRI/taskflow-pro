<?php

/**
 * Settings page: four independent forms, each posting to its own route.
 *
 * Every form carries a csrf_token field. All four routes are behind
 * CsrfMiddleware, which reads exactly that field name.
 */

use App\Http\Security;

$user = is_array($user ?? null) ? $user : [];
$currentTheme = (string) ($user['theme'] ?? 'light');
$currentLocale = (string) ($user['locale'] ?? 'fr');

$themes = ['light' => 'Clair', 'dark' => 'Sombre'];

// Mirrors UpdateLocaleUseCase::VALID_LOCALES. Note it is wider than
// Language::supported(), which lists only fr, en and ar.
$locales = [
    'fr' => 'Francais',
    'en' => 'English',
    'ar' => 'Arabe',
    'es' => 'Espanol',
    'de' => 'Deutsch',
];
?>
<h1>Parametres</h1>

<section class="settings-block" aria-labelledby="profile-heading">
    <h2 id="profile-heading">Profil</h2>

    <form method="post" action="/settings/profile">
        <input type="hidden" name="csrf_token" value="<?= Security::escape(Security::generateCsrf()) ?>">

        <label for="name">Nom</label>
        <input
            type="text"
            id="name"
            name="name"
            value="<?= Security::escape((string) ($user['name'] ?? '')) ?>"
            required
        >

        <label for="bio">Bio</label>
        <textarea id="bio" name="bio" rows="3"><?= Security::escape((string) ($user['bio'] ?? '')) ?></textarea>

        <p class="field-note">
            Email : <?= Security::escape((string) ($user['email'] ?? '')) ?>
            <span class="muted">(non modifiable ici)</span>
        </p>

        <button type="submit">Enregistrer le profil</button>
    </form>
</section>

<section class="settings-block" aria-labelledby="password-heading">
    <h2 id="password-heading">Mot de passe</h2>

    <form method="post" action="/settings/password">
        <input type="hidden" name="csrf_token" value="<?= Security::escape(Security::generateCsrf()) ?>">

        <label for="current_password">Mot de passe actuel</label>
        <input
            type="password"
            id="current_password"
            name="current_password"
            autocomplete="current-password"
            required
        >

        <label for="new_password">Nouveau mot de passe</label>
        <input
            type="password"
            id="new_password"
            name="new_password"
            autocomplete="new-password"
            minlength="8"
            required
        >

        <label for="new_password_confirmation">Confirmer</label>
        <input
            type="password"
            id="new_password_confirmation"
            name="new_password_confirmation"
            autocomplete="new-password"
            minlength="8"
            required
        >

        <button type="submit">Changer le mot de passe</button>
    </form>
</section>

<section class="settings-block" aria-labelledby="theme-heading">
    <h2 id="theme-heading">Theme</h2>

    <form method="post" action="/settings/theme">
        <input type="hidden" name="csrf_token" value="<?= Security::escape(Security::generateCsrf()) ?>">

        <label for="theme">Apparence</label>
        <select id="theme" name="theme">
            <?php foreach ($themes as $value => $label) : ?>
                <option
                    value="<?= Security::escape($value) ?>"
                    <?= $value === $currentTheme ? 'selected' : '' ?>
                ><?= Security::escape($label) ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Appliquer</button>
    </form>
</section>

<section class="settings-block" aria-labelledby="locale-heading">
    <h2 id="locale-heading">Langue</h2>

    <form method="post" action="/settings/locale">
        <input type="hidden" name="csrf_token" value="<?= Security::escape(Security::generateCsrf()) ?>">

        <label for="locale">Langue de l'interface</label>
        <select id="locale" name="locale">
            <?php foreach ($locales as $value => $label) : ?>
                <option
                    value="<?= Security::escape($value) ?>"
                    <?= $value === $currentLocale ? 'selected' : '' ?>
                ><?= Security::escape($label) ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Appliquer</button>
    </form>
</section>
