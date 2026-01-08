<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
verifierConnexion();

$utilisateur_id = getUtilisateurId();

if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}

$tache_id = $_GET['id'];

$stmt = $pdo->prepare("SELECT id FROM taches WHERE id = ? AND utilisateur_id = ?");
$stmt->execute([$tache_id, $utilisateur_id]);

if ($stmt->rowCount() > 0) {
    $stmt = $pdo->prepare("DELETE FROM taches WHERE id = ?");
    $stmt->execute([$tache_id]);
    
    $_SESSION['message'] = 'Tâche supprimée avec succès!';
    $_SESSION['message_type'] = 'success';
} else {
    $_SESSION['message'] = 'Tâche non trouvée ou non autorisée!';
    $_SESSION['message_type'] = 'error';
}

header('Location: dashboard.php');
exit();
?>

