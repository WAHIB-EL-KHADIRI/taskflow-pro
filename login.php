<?php
require_once 'includes/auth.php';

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $mot_de_passe = $_POST['mot_de_passe'];
    
    $resultat = connecterUtilisateur($email, $mot_de_passe);
    $message = $resultat['message'];
    $success = $resultat['success'];
    
    if ($success) {
        header('Location: dashboard.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Gestionnaire de Tâches</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container auth-container">
        <div class="login-form">
            <h1>Connexion</h1>
            
            <?php if ($message): ?>
                <div class="message <?php echo $success ? 'success' : 'error'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="mot_de_passe">Mot de passe:</label>
                    <input type="password" id="mot_de_passe" name="mot_de_passe" required>
                </div>
                
                <button type="submit" class="btn">Se connecter</button>
            </form>
            
            <p class="auth-link">
                Vous n'avez pas de compte? <a href="register.php">S'inscrire</a>
            </p>
        </div>
    </div>
</body>
</html>

