<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <form action="" method="post">
        <h2>Login</h2>
        <label for="matricule">Matricule:</label>
        <input type="text" id="matricule" name="matricule" required><br><br>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>

        <button type="submit">Login</button>

        <p>Don't have an account? <a href="Signin.php">Sign In</a></p>
    </form>
</body>
</html>

<?php 
session_start();
include "DB_Connexion.php";
if(isset($_POST['matricule']) && isset($_POST['password'])){
extract($_POST);

//login
$sql= $conn->prepare("SELECT id FROM users WHERE matricule = ? AND password = ?");
$sql->bind_param("ss", $matricule, $password);
$sql->execute();
$sql->bind_result($id);

if($sql->fetch()){

    header("Location: Menu.php");
} else {
    echo "Identifiants incorrects. Veuillez réessayer.";
}
}
?>
