<?php 
session_start();
include "DB_Connexion.php";

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['matricule'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Menu</title>
</head>
<body>

<?php 
try {
    $matricule = $_SESSION['matricule'];

    // Récupérer le nom de l'utilisateur
    $sql = "SELECT name FROM users WHERE matricule = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $matricule);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    echo "<p>Bonjour " . htmlspecialchars($user['name']) . "!<br>Voici les élections disponibles :</p>";

    // Récupérer toutes les élections
    $sql = "SELECT * FROM elections ORDER BY end_date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<p><strong>" . htmlspecialchars($row['title']) . "</strong></p>";
            echo "<p>Description : " . htmlspecialchars($row['description']) . "</p>";
            echo "<p>Début : " . htmlspecialchars($row['start_date']) . " | Fin : " . htmlspecialchars($row['end_date']) . "</p>";

            // Vérifier si l'élection est terminée
            $current_date = date('Y-m-d');
            if ($current_date > $row['end_date'] || $row['start_date'] === $row['end_date']) {
                echo "<p style='color: red;'>Élection terminée</p>";
                echo '<form action="resultat.php" method="get" style="display:inline;">
                        <input type="hidden" name="election_id" value="' . htmlspecialchars($row['id']) . '">
                        <button type="submit">Voir le résultat</button>
                      </form>';
            } else {
                echo "<p style='color: green;'>Élection en cours</p>";
                echo '<form action="liste_candidats.php" method="get" style="display:inline;">
                        <input type="hidden" name="election_id" value="' . htmlspecialchars($row['id']) . '">
                        <button type="submit">Voir la liste et voter</button>
                      </form>';
            }
            echo "<hr>";
        }
    } else {
        echo "<p>Aucune élection disponible pour le moment.</p>";
    }
} catch (Exception $e) {
    echo "<p>Une erreur est survenue : " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<br><a href="log.php">Se déconnecter</a>
</body>    
</html>