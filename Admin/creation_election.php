<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Création election</title>
</head>
<body>
    <ul>
        <li>
            <label for="titre">Titre de l’élection	</label>
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
            <label for="votants">Votants</label>
            <input type="text" id="votants" name="votants" placeholder="Liste des votants, séparés par des virgules" required>
        </li>
        <li>
            <button type="submit">Créer l’élection</button>
        </li>
        <li>
            <button type="button" onclick="window.location.href='Admin_dashboard.php'">Retour</button>
        </li>
    </ul>
</body>
</html>