<?php
session_start();
include "DB_Connexion.php";

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['username'])) {
    header("Location: Admin_log.php");
    exit();
}

// Vérifier si l'ID de l'élection est passé en paramètre
if (!isset($_GET['election_id'])) {
    echo "<p>Erreur : Aucun ID d'élection fourni.</p>";
    exit();
}

$election_id = intval($_GET['election_id']);

// Récupérer les informations de l'élection
$sql = "SELECT * FROM elections WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $election_id);
$stmt->execute();
$election = $stmt->get_result()->fetch_assoc();

if (!$election) {
    echo "<p>Erreur : Élection introuvable.</p>";
    exit();
}

// Mettre à jour les informations de l'élection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_election'])) {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';

    $sql = "UPDATE elections SET title = ?, description = ?, start_date = ?, end_date = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $title, $description, $start_date, $end_date, $election_id);
    $stmt->execute();

    header("Location: gestion_election.php?election_id=" . $election_id);
    exit();
}

// Supprimer l'élection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_election'])) {
    $sql = "DELETE FROM elections WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $election_id);
    $stmt->execute();

    header("Location: election_dispo.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de l'élection</title>
</head>
<body>
    <h1>Gestion de l'élection</h1>

    <form action="" method="post">
        <label for="title">Titre :</label><br>
        <input type="text" id="title" name="title" value="<?= htmlspecialchars($election['title']) ?>" required><br><br>

        <label for="description">Description :</label><br>
        <textarea id="description" name="description" rows="5" required><?= htmlspecialchars($election['description']) ?></textarea><br><br>

        <label for="start_date">Date de début :</label><br>
        <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($election['start_date']) ?>" required><br><br>

        <label for="end_date">Date de fin :</label><br>
        <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($election['end_date']) ?>" required><br><br>

        <button type="submit" name="update_election">Mettre à jour</button>
    </form>

    <form action="" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette élection ?');">
        <button type="submit" name="delete_election" style="background-color: red; color: white;">Supprimer l'élection</button>
    </form>

    <p><a href="election_dispo.php">⬅ Retour aux élections disponibles</a></p>
</body>
</html>