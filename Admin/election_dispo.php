<?php 
session_start();
include "DB_Connexion.php";

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['matricule'])) {
    header("Location: log.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Election disponible</title>
</head>
<body>

<?php 
try {
    $sql = $conn->prepare("SELECT id, title FROM elections");
    $sql->execute();
    $result = $sql->get_result();

    if ($result->num_rows > 0) {
        $matricule = $_SESSION['matricule'];
        $sql = "SELECT name FROM users WHERE matricule = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $matricule);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        echo "<p>Bonjour " . htmlspecialchars($user['name']) . "!<br>Vous pouvez gérer les candidats pour l'élection en cours suivante(s) :</p>";

        $sql = "SELECT * FROM elections WHERE start_date <= NOW() AND end_date >= NOW()";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<p>" . htmlspecialchars($row['title']) . "</p>";
                echo '<form action="gestion_candidats.php" method="get" style="display:inline;">
                        <input type="hidden" name="election_id" value="' . htmlspecialchars($row['id']) . '">
                        <button type="submit">Voir les candidats</button>
                      </form>';
            }
        } else {
            echo "<p>Aucune élection en cours.</p>";
        }
    } else {
        echo "<p>Aucune élection n'est en cours actuellement.<br>Vous recevrez une notification à l'ouverture du vote.<br>Restez connecté.</p>";
    }
} catch (Exception $e) {
    echo "<p>Une erreur est survenue : " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<br><a href="log.php">Se déconnecter</a>
</body>    
</html>
