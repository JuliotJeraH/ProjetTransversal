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
$sql = "SELECT title FROM elections WHERE id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("<p>Erreur SQL (élection) : " . htmlspecialchars($conn->error) . "</p>");
}
$stmt->bind_param("i", $election_id);
$stmt->execute();
$election = $stmt->get_result()->fetch_assoc();

if (!$election) {
    echo "<p>Erreur : Élection introuvable.</p>";
    exit();
}

// Récupérer les résultats des candidats
$sql = "SELECT name, photo, votes FROM candidates WHERE election_id = ? ORDER BY votes DESC";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("<p>Erreur SQL (candidats) : " . htmlspecialchars($conn->error) . "</p>");
}
$stmt->bind_param("i", $election_id);
$stmt->execute();
$result = $stmt->get_result();

$candidates = [];
$total_votes = 0;

while ($row = $result->fetch_assoc()) {
    $total_votes += intval($row['votes']);
    $candidates[] = $row;
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Résultats de l'élection</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 30px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border: 1px solid #ccc; text-align: left; }
        img { max-width: 100px; max-height: 100px; }
        .winner { background-color: #d4edda; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Résultats de l'élection : <?= htmlspecialchars($election['title']) ?></h1>

    <?php if (count($candidates) > 0): ?>
        <h2>🎉 Gagnant : <?= htmlspecialchars($candidates[0]['name']) ?> avec <?= intval($candidates[0]['votes']) ?> vote(s)
            (<?= $total_votes > 0 ? round(($candidates[0]['votes'] / $total_votes) * 100, 2) : 0 ?> %)
        </h2>

        <table>
            <tr>
                <th>Nom</th>
                <th>Photo</th>
                <th>Votes</th>
                <th>Pourcentage</th>
            </tr>
            <?php foreach ($candidates as $index => $candidate): ?>
                <tr class="<?= $index === 0 ? 'winner' : '' ?>">
                    <td><?= htmlspecialchars($candidate['name']) ?></td>
                    <td>
                        <?php if (!empty($candidate['photo']) && file_exists($candidate['photo'])): ?>
                            <img src="<?= htmlspecialchars($candidate['photo']) ?>" alt="Photo">
                        <?php else: ?>
                            Aucune photo
                        <?php endif; ?>
                    </td>
                    <td><?= intval($candidate['votes']) ?></td>
                    <td>
                        <?= $total_votes > 0 
                            ? round(($candidate['votes'] / $total_votes) * 100, 2) . '%' 
                            : '0%' ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Aucun candidat trouvé pour cette élection.</p>
    <?php endif; ?>

    <p><a href="election_dispo.php">⬅ Retour aux élections disponibles</a></p>
</body>
</html>
