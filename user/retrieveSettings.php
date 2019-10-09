<?php

include "/home/music/public_html/api/auth/key.php";
include "/home/music/public_html/api/auth/functions.php";
include "/home/music/public_html/api/apns/functions.php";
include "functions.php";


verifyAPIKey();

//Connect to the database
$con = mysqli_connect("localhost","music_music","Ferrari9488","music_memories");

// Check connection
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

$userID = verifyUser($con);


$sql = "SELECT dynamicMemories, dynamicMemoryDuration, addMemoriesToLibrary  FROM users WHERE id = $userID";

if ($result = $con->query($sql)) {
    $row = $result->fetch_assoc();
}
else {
    die("Error: Could not retrieve settings for user.");
}

echo json_encode($row);

$result->free();
$con->close();