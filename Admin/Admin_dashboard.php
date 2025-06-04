<?php 
session_start();
include "DB_Connexion.php";
if(!isset($_SESSION['username'])) {
    header("Location: Admin_log.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Dashboard</title>
    </head>
    <body>
    Vous ête connecté en tant que <?php echo htmlspecialchars($_SESSION['username']); ?>!<br>
    <h1>Tableau de bord Administrateur</h1>
    <ul>
        <li><a href="creation_election.php">Créer une élection</a></li>
        <li><a href="gestion_elections.php">Gérer l' élections</a></li>
        <li><a href="gestion_candidats.php">Gérer les candidats</a></li>
        <li><a href="gestion_votants.php">Gérer les votants</a></li>
        <li><a href="consult_stat.php">Consulter les statistique en temps réel</a></li>
    </ul>
    <h3>
        <p><a href="Admin_log.php">Se déconnecter</a></p>

    </body>
</html>