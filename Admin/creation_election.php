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
    } else {
        echo "Tous les champs sont requis.";
    }
}
?>
