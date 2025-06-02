<?php 
session_start();
include "DB_Connexion.php";
if(!isset($_SESSION['username'])){
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
    <h3>
        Bienvenue, <?php echo htmlspecialchars($_SESSION['username']); ?>!<br>
        <a href="Salle.php">Accéder à la salle</a><br>
        <a href="log.php">Se déconnecter</a>
    </h3>
</body>
</html>