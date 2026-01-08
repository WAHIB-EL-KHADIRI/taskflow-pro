<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
verifierConnexion();

$utilisateur_id = getUtilisateurId();
$message = '';
$success = false;

if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}

$tache_id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM taches WHERE id = ? AND utilisateur_id = ?");
$stmt->execute([$tache_id, $utilisateur_id]);
$tache = $stmt->fetch();

if (!$tache) {
    header('Location: dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre']);
    $description = trim($_POST['description']);
    $statut = $_POST['statut'];
    $date_echeance = $_POST['date_echeance'];
    $priorite = $_POST['priorite'];
    
    if (empty($titre)) {
        $message = 'Le titre est requis.';
    } else {
        $stmt = $pdo->prepare("
            UPDATE taches 
            SET titre = ?, description = ?, statut = ?, date_echeance = ?, priorite = ? 
            WHERE id = ? AND utilisateur_id = ?
        ");
        
        try {
            $stmt->execute([$titre, $description, $statut, $date_echeance, $priorite, $tache_id, $utilisateur_id]);
            $message = 'Tâche mise à jour avec succès!';
            $success = true;
            
            $tache['titre'] = $titre;
            $tache['description'] = $description;
            $tache['statut'] = $statut;
            $tache['date_echeance'] = $date_echeance;
            $tache['priorite'] = $priorite;
        } catch (PDOException $e) {
            $message = 'Erreur lors de la mise à jour: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier la Tâche - Gestionnaire de Tâches</title>
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
            <a href="add_task.php" class="nav-link">Ajouter une Tâche</a>
        </nav>
        
        <div class="form-container">
            <h2>Modifier la Tâche</h2>
            
            <?php if ($message): ?>
                <div class="message <?php echo $success ? 'success' : 'error'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="titre">Titre *</label>
                    <input type="text" id="titre" name="titre" 
                           value="<?php echo htmlspecialchars($tache['titre']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($tache['description']); ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="statut">Statut</label>
                        <select id="statut" name="statut">
                            <option value="en_attente" <?php echo $tache['statut'] === 'en_attente' ? 'selected' : ''; ?>>En attente</option>
                            <option value="en_cours" <?php echo $tache['statut'] === 'en_cours' ? 'selected' : ''; ?>>En cours</option>
                            <option value="termine" <?php echo $tache['statut'] === 'termine' ? 'selected' : ''; ?>>Terminé</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="priorite">Priorité</label>
                        <select id="priorite" name="priorite">
                            <option value="basse" <?php echo $tache['priorite'] === 'basse' ? 'selected' : ''; ?>>Basse</option>
                            <option value="moyenne" <?php echo $tache['priorite'] === 'moyenne' ? 'selected' : ''; ?>>Moyenne</option>
                            <option value="haute" <?php echo $tache['priorite'] === 'haute' ? 'selected' : ''; ?>>Haute</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="date_echeance">Date d'échéance</label>
                        <input type="date" id="date_echeance" name="date_echeance" 
                               value="<?php echo htmlspecialchars($tache['date_echeance']); ?>">
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn">Mettre à jour</button>
                    <a href="dashboard.php" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

