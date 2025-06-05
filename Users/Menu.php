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
    $sql = $conn->prepare("SELECT id, title FROM elections");
    $sql->execute();
    $result = $sql->get_result();

    if ($result->num_rows > 0) {
        $matricule = $_SESSION['matricule'];
        $sql = "SELECT has_voted, name FROM users WHERE matricule = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $matricule);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user['has_voted']) {
            echo "<p>Bonjour " . htmlspecialchars($user['name']) . "! Vous avez déjà voté.</p>";
            echo "<p>Vous avez déjà voté pour l'élection suivante :</p>";

            $sql = "SELECT title FROM elections WHERE id = (SELECT election_id FROM votes WHERE matricule = ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $matricule);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if ($row) {
                echo "<p>" . htmlspecialchars($row['title']) . "</p>";
            } else {
                echo "<p>Aucune élection trouvée.</p>";
            }
        } else {
            echo "<p>Bonjour " . htmlspecialchars($user['name']) . "!<br>Vous pouvez voter pour l'élection en cours suivante(s) :</p>";

            $sql = "SELECT * FROM elections WHERE start_date <= NOW() AND end_date >= NOW()";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<p>" . htmlspecialchars($row['title']) . "</p>";
                    echo '<form action="liste_candidats.php" method="get" style="display:inline;">
                            <input type="hidden" name="election_id" value="' . htmlspecialchars($row['id']) . '">
                            <button type="submit">Voir la liste et voter</button>
                          </form>';
                }
            } else {
                echo "<p>Aucune élection en cours.</p>";
            }
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
