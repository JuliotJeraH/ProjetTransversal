<?php
session_start();
include "DB_Connexion.php";

// Vérifie que l'utilisateur est connecté
if (!isset($_SESSION['matricule'])) {
    header("Location: log.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $candidat_id = intval($_POST['candidat_id']);
    $election_id = intval($_POST['election_id']);
    $matricule = $_SESSION['matricule'];

    // Met à jour le statut de l'utilisateur pour indiquer qu'il a voté
    $updateQuery = $conn->prepare("UPDATE users SET has_voted = 1 WHERE matricule = ?");
    $updateQuery->bind_param("s", $matricule);
    $updateQuery->execute();

    // Redirige vers la page des candidats
    header("Location: liste_candidats.php?election_id=$election_id");
    exit();
}
?>