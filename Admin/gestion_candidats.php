<?php
session_start();
include "DB_Connexion.php";

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['matricule'])) {
    header("Location: log.php");
    exit();
}

// Vérifier si l'ID de l'élection est passé en paramètre
if (!isset($_GET['election_id'])) {
    echo "<p>Erreur : Aucun ID d'élection fourni.</p>";
    exit();
}

$election_id = intval($_GET['election_id']);

// Gérer la suppression d'un candidat
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['candidate_id'])) {
    $candidate_id = intval($_POST['candidate_id']);
    try {
        $sql = "DELETE FROM candidates WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $candidate_id);
        $stmt->execute();

        // Rediriger vers la même page après suppression
        header("Location: gestion_candidats.php?election_id=" . $election_id);
        exit();
    } catch (Exception $e) {
        echo "<p>Erreur lors de la suppression du candidat : " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

// Gérer la mise à jour des visions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_all'])) {
    foreach ($_POST['visions'] as $candidate_id => $new_vision) {
        try {
            $sql = "UPDATE candidates SET vision = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $new_vision, $candidate_id);
            $stmt->execute();
        } catch (Exception $e) {
            echo "<p>Erreur lors de la mise à jour de la vision pour le candidat ID " . htmlspecialchars($candidate_id) . " : " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }

    // Rediriger vers la même page après mise à jour
    header("Location: gestion_candidats.php?election_id=" . $election_id);
    exit();
}

// Gérer l'ajout d'un nouveau candidat
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_candidate'])) {
    $name = $_POST['name'];
    $vision = $_POST['vision'];
    $photo_path = null;

    // Vérifier si une photo a été téléchargée
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $photo_tmp_name = $_FILES['photo']['tmp_name'];
        $photo_name = basename($_FILES['photo']['name']);
        $photo_path = "uploads/" . $photo_name;

        // Déplacer le fichier téléchargé vers le dossier des uploads
        if (!move_uploaded_file($photo_tmp_name, $photo_path)) {
            echo "<p>Erreur lors du téléchargement de la photo.</p>";
            $photo_path = null;
        }
    }

    // Ajouter le candidat dans la base de données
    try {
        $sql = "INSERT INTO candidates (name, photo, vision, election_id) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $name, $photo_path, $vision, $election_id);
        $stmt->execute();

        // Rediriger vers la même page après ajout
        header("Location: gestion_candidats.php?election_id=" . $election_id);
        exit();
    } catch (Exception $e) {
        echo "<p>Erreur lors de l'ajout du candidat : " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

try {
    // Récupérer les informations de l'élection
    $sql = "SELECT title FROM elections WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $election_id);
    $stmt->execute();
    $election = $stmt->get_result()->fetch_assoc();

    if (!$election) {
        echo "<p>Erreur : Élection introuvable.</p>";
        exit();
    }

    echo "<h1>Gestion des candidats pour l'élection : " . htmlspecialchars($election['title']) . "</h1>";

    // Récupérer les candidats de l'élection
    $sql = "SELECT id, name, photo, vision FROM candidates WHERE election_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $election_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<form action='' method='post'>";
        echo "<table border='1'>
                <tr>
                    <th>Nom</th>
                    <th>Photo</th>
                    <th>Vision</th>
                    <th>Actions</th>
                </tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>" . htmlspecialchars($row['name']) . "</td>
                    <td>
                        <form action='' method='post' enctype='multipart/form-data' style='display:inline;'>
                            <input type='hidden' name='update_photo_candidate_id' value='" . htmlspecialchars($row['id']) . "'>";
            if (!empty($row['photo']) && file_exists($row['photo'])) {
                // Si une photo existe, afficher la photo
                echo "<label style='cursor: pointer;'>
                        <img src='" . htmlspecialchars($row['photo']) . "' alt='Photo' width='100'>
                        <input type='file' name='new_photo' style='display:none;' onchange='this.form.submit();'>
                      </label>";
            } else {
                // Si aucune photo n'existe, afficher un champ file avec un bouton "Parcourir"
                echo "<input type='file' name='new_photo' onchange='this.form.submit();'>";
            }
            echo "      </form>
                    </td>
                    <td>
                        <textarea name='visions[" . htmlspecialchars($row['id']) . "]' rows='3' cols='30'>" . htmlspecialchars($row['vision']) . "</textarea>
                    </td>
                    <td>
                        <form action='' method='post' style='display:inline;'>
                            <input type='hidden' name='candidate_id' value='" . htmlspecialchars($row['id']) . "'>
                            <button type='submit' onclick='return confirm(\"Êtes-vous sûr de vouloir supprimer ce candidat ?\")'>Supprimer</button>
                        </form>
                    </td>
                  </tr>";
        }
        echo "</table>";
        echo "<button type='submit' name='update_all'>Valider</button>";
        echo "</form>";
    } else {
        echo "<p>Aucun candidat trouvé pour cette élection.</p>";
    }

    // Formulaire pour ajouter un nouveau candidat
    echo '<h2>Ajouter un nouveau candidat</h2>
          <form action="" method="post" enctype="multipart/form-data">
              <input type="hidden" name="add_candidate" value="1">
              <label for="name">Nom :</label>
              <input type="text" name="name" id="name" required><br>
              <label for="photo">Photo :</label>
              <input type="file" name="photo" id="photo" accept="image/*" required><br>
              <label for="vision">Vision :</label>
              <textarea name="vision" id="vision" required></textarea><br>
              <button type="submit">Ajouter</button>
          </form>';
} catch (Exception $e) {
    echo "<p>Une erreur est survenue : " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<br><a href="election_dispo.php">Retour aux élections</a>