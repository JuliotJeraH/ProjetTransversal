<?php
session_start();
include "DB_Connexion.php";

if (!isset($_SESSION['username'])) {
    header("Location: Admin_log.php");
    exit();
}

if (!isset($_GET['election_id'])) {
    echo "<p>Erreur : Aucun ID d'élection fourni.</p>";
    exit();
}

$election_id = intval($_GET['election_id']);

// Récupérer l'élection
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
    echo "<a href='resultats_election.php?election_id=" . $election_id . "'>Consulter le résultat</a>";
    exit();
}

// MISE À JOUR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_all'])) {
    foreach ($_POST['candidates'] as $candidate_id => $candidate_data) {
        $name = $candidate_data['name'] ?? '';
        $vision = $candidate_data['vision'] ?? '';
        $photo_path = null;

        if (isset($_FILES['photos']['name'][$candidate_id]) && $_FILES['photos']['error'][$candidate_id] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['photos']['name'][$candidate_id], PATHINFO_EXTENSION);
            $photo_path = "uploads/candidate_" . uniqid() . "." . $ext;
            move_uploaded_file($_FILES['photos']['tmp_name'][$candidate_id], $photo_path);

            $stmt = $conn->prepare("SELECT photo FROM candidates WHERE id = ?");
            $stmt->bind_param("i", $candidate_id);
            $stmt->execute();
            $old_photo = $stmt->get_result()->fetch_assoc()['photo'] ?? '';
            if ($old_photo && file_exists($old_photo)) {
                unlink($old_photo);
            }
        }

        if ($photo_path) {
            $stmt = $conn->prepare("UPDATE candidates SET name = ?, vision = ?, photo = ? WHERE id = ?");
            $stmt->bind_param("sssi", $name, $vision, $photo_path, $candidate_id);
        } else {
            $stmt = $conn->prepare("UPDATE candidates SET name = ?, vision = ? WHERE id = ?");
            $stmt->bind_param("ssi", $name, $vision, $candidate_id);
        }
        $stmt->execute();
    }
    header("Location: gestion_candidats.php?election_id=" . $election_id);
    exit();
}

// SUPPRESSION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_candidate_id'])) {
    $candidate_id = intval($_POST['delete_candidate_id']);
    $stmt = $conn->prepare("SELECT photo FROM candidates WHERE id = ?");
    $stmt->bind_param("i", $candidate_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    if ($res && !empty($res['photo']) && file_exists($res['photo'])) {
        unlink($res['photo']);
    }
    $stmt = $conn->prepare("DELETE FROM candidates WHERE id = ?");
    $stmt->bind_param("i", $candidate_id);
    $stmt->execute();
    header("Location: gestion_candidats.php?election_id=" . $election_id);
    exit();
}

// AJOUT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_candidate'])) {
    $name = $_POST['name'];
    $vision = $_POST['vision'];
    $photo_path = null;

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $photo_path = "uploads/candidate_" . uniqid() . "." . $ext;
        move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path);
    }

    $stmt = $conn->prepare("INSERT INTO candidates (name, photo, vision, election_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $name, $photo_path, $vision, $election_id);
    $stmt->execute();
    header("Location: gestion_candidats.php?election_id=" . $election_id);
    exit();
}

// Récupérer candidats
$stmt = $conn->prepare("SELECT id, name, photo, vision FROM candidates WHERE election_id = ?");
$stmt->bind_param("i", $election_id);
$stmt->execute();
$candidates = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des candidats</title>
</head>
<body>
    <h1>Gestion des candidats</h1>

    <?php if ($candidates->num_rows > 0): ?>
        <form action="" method="post" enctype="multipart/form-data">
            <table border="1" style="margin-bottom: 20px;">
                <tr>
                    <th>Nom</th>
                    <th>Photo</th>
                    <th>Vision</th>
                    <th>Actions</th>
                </tr>
                <?php while ($c = $candidates->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <input type="text" name="candidates[<?= $c['id'] ?>][name]" value="<?= htmlspecialchars($c['name']) ?>" required>
                        </td>
                        <td>
                            <?php if (!empty($c['photo']) && file_exists($c['photo'])): ?>
                                <img src="<?= htmlspecialchars($c['photo']) ?>" alt="Photo">
                            <?php else: ?>
                                Aucune photo
                            <?php endif; ?>
                            <br>
                            <input type="file" name="photos[<?= $c['id'] ?>]" accept="image/*">
                        </td>
                        <td>
                            <textarea name="candidates[<?= $c['id'] ?>][vision]" rows="3"><?= htmlspecialchars($c['vision']) ?></textarea>
                        </td>
                        <td>
                            <form action="" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce candidat ?')" style="display:inline;">
                                <input type="hidden" name="delete_candidate_id" value="<?= $c['id'] ?>">
                                <button type="submit">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
            <button type="submit" name="update_all">Valider</button>
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
        <button type="submit" name="add_candidate">Ajouter</button>
    </form>

    <a href="election_dispo.php">Retour aux élections</a>
</body>
</html>
