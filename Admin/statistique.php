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


// Récupérer les candidats triés par nombre de votes décroissant
$sql = "SELECT name, votes FROM candidates WHERE election_id = ? ORDER BY votes DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $election_id);
$stmt->execute();
$candidates = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques de l'élection</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        h1 { text-align: center; }
        .stat-container { display: flex; justify-content: space-around; margin-top: 20px; }
        .stat-box { border: 1px solid #ccc; padding: 20px; border-radius: 8px; text-align: center; width: 30%; }
        .stat-box h2 { margin: 0; font-size: 2em; color: #3498db; }
        .stat-box p { margin: 10px 0 0; font-size: 1.2em; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border: 1px solid #ccc; text-align: left; }
        th { background-color: #f4f4f4; }
    </style>
</head>
<body>
    <h1>Statistiques de l'élection</h1>
    <div class="stat-container">
        <div class="stat-box">
            <h2 id="total-candidates"><?= count($candidates) ?></h2>
            <p>Candidats totaux</p>
        </div>
        <div class="stat-box">
            <h2 id="total-votes">
                <?php
                $total_votes = array_sum(array_column($candidates, 'votes'));
                echo $total_votes;
                ?>
            </h2>
            <p>Votes totaux</p>
        </div>
    </div>

    <h2>Classement des candidats</h2>
    <table>
        <tr>
            <th>Nom</th>
            <th>Votes</th>
        </tr>
        <?php foreach ($candidates as $candidate): ?>
            <tr>
                <td><?= htmlspecialchars($candidate['name']) ?></td>
                <td><?= intval($candidate['votes']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>