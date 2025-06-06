<?php 
session_start();
include "DB_Connexion.php";

// Vérifie que l'utilisateur est connecté
if (!isset($_SESSION['matricule'])) {
    header("Location: login.php");
    exit();
}

// Vérifie si une élection a été choisie
if (!isset($_GET['election_id'])) {
    echo "Veuillez choisir une élection.";
    exit();
}

$election_id = intval($_GET['election_id']); // sécurité : forcer entier

// Récupérer la liste des candidates pour cette élection
$query = $conn->prepare("SELECT id, name, vision, photo FROM candidates WHERE election_id = ?");
$query->bind_param("i", $election_id);
$query->execute();
$result = $query->get_result();

$candidates = [];
while ($row = $result->fetch_assoc()) {
    $candidates[] = $row;
}

// Vérifie si l'utilisateur a déjà voté
$matricule = $_SESSION['matricule'];
$hasVotedQuery = $conn->prepare("SELECT has_voted FROM users WHERE matricule = ?");
$hasVotedQuery->bind_param("s", $matricule);
$hasVotedQuery->execute();
$hasVotedResult = $hasVotedQuery->get_result();
$hasVoted = $hasVotedResult->fetch_assoc()['has_voted'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des candidates</title>
</head>
<body>
    <h1>Liste des candidates</h1>

    <?php if (!empty($candidates)): ?>
        <table border="1">
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Nom</th>
                    <th>Vision</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($candidates as $candidate): ?>
                    <tr>
                        <td>
                            <?php if (!empty($candidate['photo'])): ?>
                                <img src="<?php echo htmlspecialchars($candidate['photo']); ?>" alt="Photo de <?php echo htmlspecialchars($candidate['name']); ?>" width="50" height="50">
                            <?php else: ?>
                                <span>Pas de photo</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($candidate['name']); ?></td>
                        <td><?php echo htmlspecialchars($candidate['vision']); ?></td>
                        <td>
                            <?php if (!$hasVoted): ?>
                                <form action="trait_voter.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="candidat_id" value="<?php echo $candidate['id']; ?>">
                                    <input type="hidden" name="election_id" value="<?php echo $election_id; ?>">
                                    <button type="submit">Voter</button>
                                </form>
                            <?php else: ?>
                                <button type="button" disabled>Vote enregistré</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <br>
        <?php if ($hasVoted): ?>
            <form action="trait_modifvote.php" method="POST">
                <input type="hidden" name="election_id" value="<?php echo $election_id; ?>">
                <button type="submit">Modifier le vote</button>
            </form>
        <?php endif; ?>
    <?php else: ?>
        <p>Aucune candidate trouvée pour cette élection.</p>
    <?php endif; ?>

    <br><a href="menu.php">Retour au menu</a>
</body>
</html>
