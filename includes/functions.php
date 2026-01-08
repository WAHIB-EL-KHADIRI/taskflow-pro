<?php
function estConnecte() {
    return isset($_SESSION['utilisateur_id']);
}
function verifierConnexion() {
    if (!estConnecte()) {
        header('Location: login.php');
        exit();
    }
}
function getUtilisateurId() {
    return $_SESSION['utilisateur_id'] ?? null;
}
function formaterDate($date) {
    if (!$date) return 'Non définie';
    return date('d/m/Y', strtotime($date));
}
function getClasseStatut($statut) {
    switch ($statut) {
        case 'en_attente':
            return 'statut-attente';
        case 'en_cours':
            return 'statut-cours';
        case 'termine':
            return 'statut-termine';
        default:
            return '';
    }
}
function getTexteStatut($statut) {
    $statuts = [
        'en_attente' => 'En attente',
        'en_cours' => 'En cours',
        'termine' => 'Terminé'
    ];
    return $statuts[$statut] ?? $statut;
}
function getClassePriorite($priorite) {
    switch ($priorite) {
        case 'basse':
            return 'priorite-basse';
        case 'moyenne':
            return 'priorite-moyenne';
        case 'haute':
            return 'priorite-haute';
        default:
            return '';
    }
}
function getTextePriorite($priorite) {
    $priorites = [
        'basse' => 'Basse',
        'moyenne' => 'Moyenne',
        'haute' => 'Haute'
    ];
    return $priorites[$priorite] ?? $priorite;
}
?>

