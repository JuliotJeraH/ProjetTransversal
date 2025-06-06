<?php
session_start();
include "DB_Connexion.php";

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['username'])) {
    header("Location: Admin_log.php");
    exit();
}

// Vérifier si l'ID de l'élection est passé en paramètre
if (!isset($_GET['election_id'])) {
    echo "<p>Erreur : Aucun ID d'élection fourni.</p>";
    exit();
}

$election_id = intval($_GET['election_id']);

// Récupérer les informations de l'élection
$sql = "SELECT title, end_date FROM elections WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $election_id);
$stmt->execute();
$election = $stmt->get_result()->fetch_assoc();

if (!$election) {
    echo "<p>Erreur : Élection introuvable.</p>";
    exit();
}

$current_date = date('Y-m-d');
$is_election_ended = $current_date > $election['end_date'];

echo "<h1>Gestion des candidats pour l'élection : " . htmlspecialchars($election['title']) . "</h1>";

if ($is_election_ended) {
    echo "<p style='color: red; font-weight: bold;'>Cette élection est terminée.</p>";
    echo "<a href='resultats_election.php?election_id=" . $election_id . "' style='background-color: #FFC107; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>Consulter le résultat</a>";
    exit();
}

// Gérer la mise à jour des visions des candidats
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_all'])) {
    foreach ($_POST['visions'] as $candidate_id => $new_vision) {
        $sql = "UPDATE candidates SET vision = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $new_vision, $candidate_id);
        $stmt->execute();
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

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $photo_path = "uploads/candidate_" . uniqid() . "." . $ext;
        move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path);
    }

    $sql = "INSERT INTO candidates (name, photo, vision, election_id) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $name, $photo_path, $vision, $election_id);
    $stmt->execute();
    header("Location: gestion_candidats.php?election_id=" . $election_id);
    exit();
}

// Récupérer les candidats de l'élection
$sql = "SELECT id, name, photo, vision FROM candidates WHERE election_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $election_id);
$stmt->execute();
$candidates = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des candidats</title>
    <style>
        table { border-collapse: collapse; width: 100%; max-width: 900px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        img { max-width: 100px; max-height: 100px; object-fit: contain; }
        textarea { width: 100%; }
        input[type="text"] { width: 100%; }
        .btn { padding: 6px 12px; margin: 2px; cursor: pointer; }
        .btn-delete { background: #e74c3c; color: white; border: none; }
        .btn-update { background: #3498db; color: white; border: none; }
        .btn-add { background: #2ecc71; color: white; border: none; }
    </style>
</head>
<body>
    <h1>Gestion des candidats</h1>

    <?php if ($candidates->num_rows > 0): ?>
        <form action="" method="post">
            <table border="1">
                <tr>
                    <th>Nom</th>
                    <th>Photo</th>
                    <th>Vision</th>
                    <th>Actions</th>
                </tr>
                <?php while ($c = $candidates->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['name']) ?></td>
                        <td>
                            <?php if (!empty($c['photo']) && file_exists($c['photo'])): ?>
                                <img src="<?= htmlspecialchars($c['photo']) ?>" alt="Photo" width="100">
                            <?php else: ?>
                                Aucune photo
                            <?php endif; ?>
                        </td>
                        <td>
                            <textarea name="visions[<?= $c['id'] ?>]" rows="3" cols="30"><?= htmlspecialchars($c['vision']) ?></textarea>
                        </td>
                        <td>
                            <form action="" method="post" style="display:inline;">
                                <input type="hidden" name="delete_candidate_id" value="<?= $c['id'] ?>">
                                <button type="submit" class="btn btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce candidat ?')">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
            <button type="submit" name="update_all" class="btn btn-update" style="margin-top: 10px;">Valider les modifications</button>
        </form>
    <?php else: ?>
        <p>Aucun candidat trouvé pour cette élection.</p>
    <?php endif; ?>

    <h2>Ajouter un nouveau candidat</h2>
    <form action="" method="post" enctype="multipart/form-data">
        <label for="name">Nom :</label>
        <input type="text" name="name" id="name" required><br>
        <label for="photo">Photo :</label>
        <input type="file" name="photo" id="photo" accept="image/*" required><br>
        <label for="vision">Vision :</label>
        <textarea name="vision" id="vision" required></textarea><br>
        <button type="submit" name="add_candidate" class="btn btn-add">Ajouter</button>
    </form>

    <a href="election_dispo.php">Retour aux élections</a>
</body>
</html>