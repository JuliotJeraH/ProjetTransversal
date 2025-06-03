<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin login</title>
</head>
<body>
    <form action="" method="post">
        <h2>Admin Login</h2>
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required><br><br>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>

        <button type="submit">Login</button>

    </form>

    <?php 
    session_start();
    include "DB_Connexion.php";
    if(isset($_POST['username']) && isset($_POST['password'])){
        extract($_POST);

        //login
        $sql= $conn->prepare("SELECT id FROM admin WHERE nom = ? AND mot_de_passe = ?");
        $sql->bind_param("ss", $username, $password);
        $sql->execute();
        $sql->bind_result($id);

        if($sql->fetch()){
            $_SESSION["username"]=$username;
            header("Location: Admin_dashboard.php");
        } else {
            echo "Identifiants incorrects. Veuillez réessayer.";
        }
    }
    ?>
</body>
</html>