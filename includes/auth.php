<?php
require_once 'config/database.php';

function inscrireUtilisateur($nom, $email, $mot_de_passe) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() > 0) {
        return ['success' => false, 'message' => 'Cet email est déjà utilisé.'];
    }
    
    $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, email, mot_de_passe) VALUES (?, ?, ?)");
    $result = $stmt->execute([$nom, $email, $mot_de_passe_hash]);
    
    if ($result) {
        return ['success' => true, 'message' => 'Inscription réussie!'];
    } else {
        return ['success' => false, 'message' => 'Erreur lors de l\'inscription.'];
    }
}
function connecterUtilisateur($email, $mot_de_passe) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT id, nom, email, mot_de_passe FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    $utilisateur = $stmt->fetch();
    
    if ($utilisateur && password_verify($mot_de_passe, $utilisateur['mot_de_passe'])) {
        $_SESSION['utilisateur_id'] = $utilisateur['id'];
        $_SESSION['utilisateur_nom'] = $utilisateur['nom'];
        $_SESSION['utilisateur_email'] = $utilisateur['email'];
        return ['success' => true, 'message' => 'Connexion réussie!'];
    } else {
        return ['success' => false, 'message' => 'Email ou mot de passe incorrect.'];
    }
}
function deconnecterUtilisateur() {
    session_destroy();
    header('Location: login.php');
    exit();
}
?>

