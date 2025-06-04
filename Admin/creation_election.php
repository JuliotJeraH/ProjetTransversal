<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Création élection</title>
</head>
<body>
    <form method="post" action="">
        <ul>
            <li>
                <label for="titre">Titre de l’élection</label>
                <input type="text" id="titre" name="titre" required>
            </li>
            <li>
                <label for="date_debut">Date de début</label>
                <input type="date" id="date_debut" name="date_debut" required>
            </li>
            <li>
                <label for="date_fin">Date de fin</label>
                <input type="date" id="date_fin" name="date_fin" required>
            </li>
            <li>
                <label for="description">Description</label>
                <textarea id="description" name="description" required></textarea>
            </li>
            <li>
                <label for="candidats">Candidats</label>
                <input type="text" id="candidats" name="candidats" placeholder="Liste des candidats, séparés par des virgules" required>
            </li>
            <li>
                <input type="submit" value="Créer l’élection">
            </li>
            <li>
                <button type="button" onclick="window.location.href='Admin_dashboard.php'">Retour</button>
            </li>
        </ul>
    </form>
</body>
</html>
<?php 
session_start();
include "DB_Connexion.php";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST['titre']) && isset($_POST['date_debut']) && isset($_POST['date_fin']) && isset($_POST['description']) && isset($_POST['candidats'])){
        extract($_POST);
        
        $sql = $conn->prepare("SELECT id FROM elections WHERE title = ?");
        $sql->bind_param("s", $titre);
        $sql->execute();
        $sql->store_result();
        
        if($sql->num_rows > 0) {
            echo "Une élection avec ce titre existe déjà. Veuillez choisir un titre différent.";
        } else {
            $sql = $conn->prepare("INSERT INTO elections (title, description, start_date, end_date, candidats) VALUES (?, ?, ?, ?, ?)");
            $sql->bind_param("sssss", $titre, $description, $date_debut, $date_fin, $candidats);
            
            if($sql->execute()) {
                echo "Élection créée avec succès!";

            } else {
                echo "Erreur lors de la création de l'élection: " . $conn->error;
            }
        }
    }
}
?>
