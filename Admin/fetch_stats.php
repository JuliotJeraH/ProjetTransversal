<?php
session_start();
include "DB_Connexion.php";

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['username'])) {
    header("HTTP/1.1 403 Forbidden");
    exit();
}

// Vérifier si l'ID de l'élection est passé en paramètre
if (!isset($_GET['election_id'])) {
    echo json_encode(["error" => "Aucun ID d'élection fourni."]);
    exit();
}

$election_id = intval($_GET['election_id']);

// Récupérer les statistiques pour l'élection donnée
$response = [
    "total_candidates" => 0,
    "total_votes" => 0,
];

$sql = "SELECT COUNT(*) AS total FROM candidates WHERE election_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $election_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $row = $result->fetch_assoc()) {
    $response["total_candidates"] = $row["total"];
}

$sql = "SELECT SUM(votes) AS total FROM candidates WHERE election_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $election_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $row = $result->fetch_assoc()) {
    $response["total_votes"] = $row["total"] ?? 0;
}

// Retourner les statistiques en JSON
header("Content-Type: application/json");
echo json_encode($response);
?>