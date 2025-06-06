<?php
session_start();
include "DB_Connexion.php";

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['username'])) {
    header("Location: Admin_log.php");
    exit();
}

// Récupérer toutes les élections
$sql = "SELECT id, title, start_date, end_date FROM elections ORDER BY start_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des élections</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 30px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        a.button {
            padding: 8px 12px;
            background-color: #007BFF;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        a.button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h1>📋 Liste des élections</h1>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>Titre</th>
                <th>Date de début</th>
                <th>Date de fin</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= htmlspecialchars($row['start_date']) ?></td>
                    <td><?= htmlspecialchars($row['end_date']) ?></td>
                    <td>
                        <a class="button" href="statistique.php?election_id=<?= $row['id'] ?>">
                            Voir la statistique
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>Aucune élection trouvée.</p>
    <?php endif; ?>

    <p><a href="dashboard.php">⬅ Retour au tableau de bord</a></p>
</body>
</html>
