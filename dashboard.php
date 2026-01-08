<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
verifierConnexion();

$utilisateur_id = getUtilisateurId();

$statut = $_GET['statut'] ?? 'tous';
$recherche = $_GET['recherche'] ?? '';

$sql = "SELECT * FROM taches WHERE utilisateur_id = ?";
$params = [$utilisateur_id];

if ($statut !== 'tous') {
    $sql .= " AND statut = ?";
    $params[] = $statut;
}

if (!empty($recherche)) {
    $sql .= " AND (titre LIKE ? OR description LIKE ?)";
    $params[] = "%$recherche%";
    $params[] = "%$recherche%";
}

$sql .= " ORDER BY 
    CASE priorite 
        WHEN 'haute' THEN 1 
        WHEN 'moyenne' THEN 2 
        WHEN 'basse' THEN 3 
    END, 
    date_echeance ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$taches = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT statut, COUNT(*) as count 
    FROM taches 
    WHERE utilisateur_id = ? 
    GROUP BY statut
");
$stmt->execute([$utilisateur_id]);
$stats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Gestionnaire de Tâches</title>
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
            <a href="dashboard.php" class="nav-link active">Tableau de Bord</a>
            <a href="add_task.php" class="nav-link">Ajouter une Tâche</a>
        </nav>
        
        <div class="stats">
            <div class="stat-card">
                <h3>En attente</h3>
                <p class="stat-count"><?php echo $stats['en_attente'] ?? 0; ?></p>
            </div>
            <div class="stat-card">
                <h3>En cours</h3>
                <p class="stat-count"><?php echo $stats['en_cours'] ?? 0; ?></p>
            </div>
            <div class="stat-card">
                <h3>Terminées</h3>
                <p class="stat-count"><?php echo $stats['termine'] ?? 0; ?></p>
            </div>
        </div>
        
        <div class="filters">
            <form method="GET" class="filter-form">
                <select name="statut" onchange="this.form.submit()">
                    <option value="tous" <?php echo $statut === 'tous' ? 'selected' : ''; ?>>Toutes les tâches</option>
                    <option value="en_attente" <?php echo $statut === 'en_attente' ? 'selected' : ''; ?>>En attente</option>
                    <option value="en_cours" <?php echo $statut === 'en_cours' ? 'selected' : ''; ?>>En cours</option>
                    <option value="termine" <?php echo $statut === 'termine' ? 'selected' : ''; ?>>Terminées</option>
                </select>
                
                <input type="text" name="recherche" placeholder="Rechercher une tâche..." 
                       value="<?php echo htmlspecialchars($recherche); ?>">
                <button type="submit" class="btn">Rechercher</button>
                <a href="dashboard.php" class="btn btn-secondary">Réinitialiser</a>
            </form>
        </div>
        
        <div class="tasks-list">
            <?php if (empty($taches)): ?>
                <p class="no-tasks">Aucune tâche trouvée.</p>
            <?php else: ?>
                <?php foreach ($taches as $tache): ?>
                    <div class="task-card">
                        <div class="task-header">
                            <h3><?php echo htmlspecialchars($tache['titre']); ?></h3>
                            <div class="task-actions">
                                <a href="edit_task.php?id=<?php echo $tache['id']; ?>" class="btn-edit">Modifier</a>
                                <a href="delete_task.php?id=<?php echo $tache['id']; ?>" 
                                   class="btn-delete" 
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette tâche?')">
                                    Supprimer
                                </a>
                            </div>
                        </div>
                        
                        <p class="task-description"><?php echo nl2br(htmlspecialchars($tache['description'])); ?></p>
                        
                        <div class="task-meta">
                            <span class="badge <?php echo getClasseStatut($tache['statut']); ?>">
                                <?php echo getTexteStatut($tache['statut']); ?>
                            </span>
                            
                            <span class="badge <?php echo getClassePriorite($tache['priorite']); ?>">
                                <?php echo getTextePriorite($tache['priorite']); ?>
                            </span>
                            
                            <span class="task-date">
                                Échéance: <?php echo formaterDate($tache['date_echeance']); ?>
                            </span>
                            
                            <span class="task-date">
                                Créée le: <?php echo date('d/m/Y', strtotime($tache['date_creation'])); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

