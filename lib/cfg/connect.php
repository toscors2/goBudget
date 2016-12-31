<?php
    header("Access-Control-Allow-Origin: *");

    $server = "198.71.227.89:3306";
    $user = "scriptjet";
    $password = "12Trustno1";
    $database = "budget";

    $conn = mysqli_connect($server, $user, $password, $database);

    if(!$conn) {
        die("connection failed: " . mysqli_connect_error());
    }

?>