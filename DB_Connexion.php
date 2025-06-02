<?php
$host= "localhost";
$user= "root";
$password= "";
$dbname= "votedelegue";
$conn= new mysqli($host, $user, $password, $dbname);

if($conn->connect_error){
    die("Echec de la connexion:". $conn->connect_error);
}

?>