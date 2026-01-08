<?php
require_once 'includes/auth.php';

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $mot_de_passe = $_POST['mot_de_passe'];
    $confirmation_mot_de_passe = $_POST['confirmation_mot_de_passe'];
    
    if (empty($nom) || empty($email) || empty($mot_de_passe)) {
        $message = 'Tous les champs sont requis.';
    } elseif ($mot_de_passe !== $confirmation_mot_de_passe) {
        $message = 'Les mots de passe ne correspondent pas.';
    } elseif (strlen($mot_de_passe) < 6) {
        $message = 'Le mot de passe doit contenir au moins 6 caractères.';
    } else {
        $resultat = inscrireUtilisateur($nom, $email, $mot_de_passe);
        $message = $resultat['message'];
        $success = $resultat['success'];
        
        if ($success) {
            header('Location: login.php?inscription=success');
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Gestionnaire de Tâches</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container auth-container">
        <div class="login-form">
            <h1>Inscription</h1>
            
            <?php if ($message): ?>
                <div class="message <?php echo $success ? 'success' : 'error'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="nom">Nom complet:</label>
                    <input type="text" id="nom" name="nom" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="mot_de_passe">Mot de passe:</label>
                    <input type="password" id="mot_de_passe" name="mot_de_passe" required>
                </div>
                
                <div class="form-group">
                    <label for="confirmation_mot_de_passe">Confirmer le mot de passe:</label>
                    <input type="password" id="confirmation_mot_de_passe" name="confirmation_mot_de_passe" required>
                </div>
                
                <button type="submit" class="btn">S'inscrire</button>
            </form>
            
            <p class="auth-link">
                Vous avez déjà un compte? <a href="login.php">Se connecter</a>
            </p>
        </div>
    </div>
</body>
</html>

