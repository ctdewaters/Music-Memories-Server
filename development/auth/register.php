<?php
include_once "functions.php";
include "key.php";

verifyAPIKey();

//Connect to the database
$con = mysqli_connect("localhost","music_music","Ferrari9488","music_memories");

// Check connection
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

if (isset($_GET["appleID"])) {
    $appleID = mysqli_real_escape_string($con, $_GET['appleID']);
}
else {
    die("ERROR: Identifying value (username/Apple ID) not received!");
}

if (isset($_GET['password'])) {
    $JWT = mysqli_real_escape_string($con, $_GET["password"]);
    $decodedJWT = decodeJWT($JWT);

    $iss = $decodedJWT->iss;
    $aud = $decodedJWT->aud;
    $sub = $decodedJWT->sub;

    if ($iss != "https://appleid.apple.com" || $aud != "com.CollinDeWaters.Music-Memories" || $sub != $appleID) {
        die("Error: Invalid JWT token passed.");
    }

    $rawPassword = $iss . "%^#" . $sub . "@!*" . $aud;
    $passwordHash = password_hash($rawPassword, PASSWORD_DEFAULT);
}
else {
    die("Error: No password value received.");
}

$sql = "INSERT INTO users (password, appleID) VALUES ('$passwordHash', '$appleID')";

if($con->query($sql)) {
    echo "Successfully registered user!";

}
else {
    $userID = verifyUser($con);

    echo "Successfully verified user!";
}

$result->free();
$con->close();

