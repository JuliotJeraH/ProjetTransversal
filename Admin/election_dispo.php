<?php
session_start();
include "DB_Connexion.php";

// Vérifier connexion admin (exemple basique)
if (!isset($_SESSION['username'])) {
    header("Location: Admin_log.php");
    exit();
}

$sql = "SELECT id, title, description, start_date, end_date FROM elections ORDER BY start_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Élections disponibles</title>
</head>
<body>
    <h1>Élections disponibles</h1>

    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div>
                <h2><?= htmlspecialchars($row['title']) ?></h2>
                <p><strong>Description :</strong> <?= nl2br(htmlspecialchars($row['description'])) ?></p>
                <p><strong>Date de début :</strong> <?= htmlspecialchars($row['start_date']) ?></p>
                <p><strong>Date de fin :</strong> <?= htmlspecialchars($row['end_date']) ?></p>

                <?php
                // Vérifier si l'élection est terminée
                $current_date = date('Y-m-d');
                if ($current_date > $row['end_date']): ?>
                    <p>Élection terminée</p>
                    <p>
                        <a href="resultats_election.php?election_id=<?= $row['id'] ?>">Consulter le résultat</a>
                    </p>
                <?php else: ?>
                    <p>
                        <a href="gestion_candidats.php?election_id=<?= $row['id'] ?>">Gérer les candidats</a>
                    </p>
                    <p>
                        <a href="gestion_election.php?election_id=<?= $row['id'] ?>">Gérer l'élection</a>
                    </p>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>Aucune élection disponible pour le moment.</p>
    <?php endif; ?>

    <p><a href="Admin_dashboard.php">⬅ Retour au tableau de bord</a></p>
</body>
</html>
