<?php 
session_start();
include "DB_Connexion.php";

// Vérifier si l'utilisateur est connecté
if(!isset($_SESSION['matricule'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu</title>
</head>
<body>

        <?php 
         $sql = $conn->prepare("SELECT id FROM elections");
            $sql->execute();
            $result = $sql->get_result();
            if ($result->num_rows > 0) {
                $matricule=$_SESSION['matricule'];
                $sql = "SELECT has_voted FROM users WHERE matricule = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $matricule);
                $stmt->execute();
                $user = $stmt->get_result()->fetch_assoc();
                
                if ($user['has_voted']) {
                    echo "<p>Bonjour ".$user['name']."! Vous avez déjà voté.</p>
                    <p>Vous avez déjà voté pour l'éléction:<p>";
                    $sql = "SELECT id FROM elections";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row= $result->fetch_assoc();
                    $election_title = $row['title'];
                    echo "<p>".$election_title."</p>";
                } else if (!$user['has_voted']) {
                    $sql = "SELECT name FROM users WHERE matricule = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("s", $matricule);
                    $stmt->execute();
                    $user = $stmt->get_result()->fetch_assoc();
                    echo "<p>Bonjour " . $user['name'] . "! <br>
                    
                    Vous pouvez voter pour l'élection en cours suivant(s): <br>";
                    $sql = "SELECT title FROM elections WHERE start_date <= NOW() AND end_date >= NOW()";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<p>" . htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8') . "</p>";
                        }
                    } else {
                        echo "<p>Aucune élection en cours.</p>";
                    }
        
            } else {
                $matricule=$_SESSION['matricule'];
                $sql = "SELECT name FROM users WHERE matricule = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $matricule);
                $stmt->execute();
                $user = $stmt->get_result()->fetch_assoc();
                
                if ($user) {
                    echo "<p>Bonjour " . htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') . "! <br>
                    Aucune élection n'est en cours actuellement. <br>
                    Vous recevrez une notification à l'ouverture du vote. <br>
                    Restez connecté.</p>";
                } else {
                    echo "<p>Utilisateur introuvable.</p>";
                }

            }
        }
    
        ?>



    <a href="log.php">Se déconnecter</a>
</body>
</html>