<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>
<body>
    <form action="" method="post">
        <h2>Sign In</h2>
        <label for="matricule">Matricule:</label>
        <input type="text" id="matricule" name="matricule" required><br><br>


        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required><br><br>

        
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br><br>


        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>


        <button type="submit">Sign In</button>

        <p>Already have an account? <a href="log.php">Login</a></p>
    </form>
</body>
</html>

<?php 

session_start();
include "DB_Connexion.php";
if(isset($_POST['matricule']) && isset($_POST['username']) && isset($_POST['email']) && isset($_POST['password'])){
extract($_POST);



$sql= $conn->prepare("SELECT id FROM users WHERE matricule = ?");
    $sql->bind_param("s",$matricule);
    $sql->execute();
    $sql->bind_result($id);

    if($sql->fetch()){
        echo "Vous avez déjà un compte";
    }else{
        $_SESSION['username'] = $username;

$sql= $conn->prepare("INSERT INTO users (matricule,name,email,password) VALUES (?,?,?,?)");
$sql->bind_param("ssss", $matricule, $username, $email, $password);

if($sql->execute()){
    echo "Vous êtes inscrit avec succès, " . $username . "!<br>";
}else{
    echo "Erreur:". $conn->error;
}

}
}
?>
