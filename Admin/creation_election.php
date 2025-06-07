<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Création d'une élection</title>
</head>
<body>
    <h1>Création d'une élection</h1>
    <form method="POST" action="">
        <label for="titre">Titre :</label>
        <input type="text" name="titre" id="titre" required><br>

        <label for="date_debut">Date de début :</label>
        <input type="date" name="date_debut" id="date_debut" required><br>

        <label for="date_fin">Date de fin :</label>
        <input type="date" name="date_fin" id="date_fin" required><br>

        <label for="description">Description :</label>
        <textarea name="description" id="description" required></textarea><br>

        <label for="candidates">Candidats (séparés par des virgules) :</label>
        <input type="text" name="candidates" id="candidates" required><br>

        <button type="submit">Créer l'élection</button>
    </form>
    <p><a href="Admin_dashboard.php">Retour</a></p>
</body>
</html>

<?php 
session_start();
include "DB_Connexion.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['titre'], $_POST['date_debut'], $_POST['date_fin'], $_POST['description'], $_POST['candidates'])) {
        // Extract form data
        $titre = trim($_POST['titre']);
        $date_debut = trim($_POST['date_debut']);
        $date_fin = trim($_POST['date_fin']);
        $description = trim($_POST['description']);
        $candidates = trim($_POST['candidates']);

        // Check if there is an ongoing election
        $current_date = date("Y-m-d");
        $ongoing_sql = $conn->prepare("SELECT id FROM elections WHERE end_date >= ?");
        if (!$ongoing_sql) {
            die("Erreur lors de la préparation de la requête: " . $conn->error);
        }
        $ongoing_sql->bind_param("s", $current_date);
        $ongoing_sql->execute();
        $ongoing_sql->store_result();

        if ($ongoing_sql->num_rows > 0) {
            echo "Une élection est encore en cours. Vous ne pouvez pas en créer une nouvelle tant qu'elle n'est pas terminée.";
        } else {
            // Check if an election with the same title already exists
            $sql = $conn->prepare("SELECT id FROM elections WHERE title = ?");
            if (!$sql) {
                die("Erreur lors de la préparation de la requête: " . $conn->error);
            }
            $sql->bind_param("s", $titre);
            $sql->execute();
            $sql->store_result();

            if ($sql->num_rows > 0) {
                echo "Une élection avec ce titre existe déjà. Veuillez choisir un titre différent.";
            } else {
                // Insert the election into the elections table
                $sql = $conn->prepare("INSERT INTO elections (title, description, start_date, end_date) VALUES (?, ?, ?, ?)");
                if (!$sql) {
                    die("Erreur lors de la préparation de la requête: " . $conn->error);
                }
                $sql->bind_param("ssss", $titre, $description, $date_debut, $date_fin);

                if ($sql->execute()) {
                    $election_id = $conn->insert_id; // Get the inserted election ID

                    // Insert candidates into the candidates table
                    $candidates_array = explode(',', $candidates);
                    $candidate_sql = $conn->prepare("INSERT INTO candidates (election_id, name) VALUES (?, ?)");
                    if (!$candidate_sql) {
                        die("Erreur lors de la préparation de la requête pour les candidats: " . $conn->error);
                    }

                    foreach ($candidates_array as $candidate) {
                        $candidate = trim($candidate); // Remove extra spaces
                        $candidate_sql->bind_param("is", $election_id, $candidate);
                        if (!$candidate_sql->execute()) {
                            die("Erreur lors de l'insertion du candidat '$candidate': " . $candidate_sql->error);
                        }
                    }

                    echo "Élection et candidats créés avec succès!";
                } else {
                    echo "Erreur lors de la création de l'élection: " . $conn->error;
                }
            }
        }
    } else {
        echo "Tous les champs sont requis.";
    }
}
?>
