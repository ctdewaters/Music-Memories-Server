<?php

include "/home/music/public_html/api/auth/key.php";
include "/home/music/public_html/api/auth/functions.php";

verifyAPIKey();

//Connect to the database
$con = mysqli_connect("localhost","music_music","Ferrari9488","music_memories");

// Check connection
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

verifyUser($con);

$payload = stripcslashes(mysqli_real_escape_string($con, $_GET["payload"]));
$payloadArray = json_decode($payload);
print_r($payloadArray);
