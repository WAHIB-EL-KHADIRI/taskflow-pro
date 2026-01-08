<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
verifierConnexion();

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre']);
    $description = trim($_POST['description']);
    $statut = $_POST['statut'];
    $date_echeance = $_POST['date_echeance'];
    $priorite = $_POST['priorite'];
    $utilisateur_id = getUtilisateurId();
    
    if (empty($titre)) {
        $message = 'Le titre est requis.';
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO taches (utilisateur_id, titre, description, statut, date_echeance, priorite) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        try {
            $stmt->execute([$utilisateur_id, $titre, $description, $statut, $date_echeance, $priorite]);
            $message = 'Tâche ajoutée avec succès!';
            $success = true;
            
            $_POST = [];
        } catch (PDOException $e) {
            $message = 'Erreur lors de l\'ajout de la tâche: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une Tâche - Gestionnaire de Tâches</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Gestionnaire de Tâches</h1>
            <div class="user-info">
                <span>Bonjour, <?php echo htmlspecialchars($_SESSION['utilisateur_nom']); ?>!</span>
                <a href="logout.php" class="btn btn-logout">Déconnexion</a>
            </div>
        </header>
        
        <nav class="main-nav">
            <a href="dashboard.php" class="nav-link">Tableau de Bord</a>
            <a href="add_task.php" class="nav-link active">Ajouter une Tâche</a>
        </nav>
        
        <div class="form-container">
            <h2>Ajouter une Nouvelle Tâche</h2>
            
            <?php if ($message): ?>
                <div class="message <?php echo $success ? 'success' : 'error'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="titre">Titre *</label>
                    <input type="text" id="titre" name="titre" 
                           value="<?php echo htmlspecialchars($_POST['titre'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="statut">Statut</label>
                        <select id="statut" name="statut">
                            <option value="en_attente" <?php echo ($_POST['statut'] ?? '') === 'en_attente' ? 'selected' : ''; ?>>En attente</option>
                            <option value="en_cours" <?php echo ($_POST['statut'] ?? '') === 'en_cours' ? 'selected' : ''; ?>>En cours</option>
                            <option value="termine" <?php echo ($_POST['statut'] ?? '') === 'termine' ? 'selected' : ''; ?>>Terminé</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="priorite">Priorité</label>
                        <select id="priorite" name="priorite">
                            <option value="basse" <?php echo ($_POST['priorite'] ?? '') === 'basse' ? 'selected' : ''; ?>>Basse</option>
                            <option value="moyenne" <?php echo ($_POST['priorite'] ?? '') === 'moyenne' ? 'selected' : ''; ?>>Moyenne</option>
                            <option value="haute" <?php echo ($_POST['priorite'] ?? '') === 'haute' ? 'selected' : ''; ?>>Haute</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="date_echeance">Date d'échéance</label>
                        <input type="date" id="date_echeance" name="date_echeance" 
                               value="<?php echo htmlspecialchars($_POST['date_echeance'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn">Ajouter la Tâche</button>
                    <a href="dashboard.php" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

