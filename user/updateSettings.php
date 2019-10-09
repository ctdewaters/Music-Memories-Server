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
$apnsToken =  mysqli_real_escape_string($con, $_GET["apnsToken"]);;

$dynamicMemories = intval(mysqli_real_escape_string($con, $_GET["dynamicMemories"]));
$duration = intval(mysqli_real_escape_string($con, $_GET["duration"]));
$addToLibrary = intval(mysqli_real_escape_string($con, $_GET["addToLibrary"]));

$sql = "UPDATE users SET dynamicMemories = $dynamicMemories, dynamicMemoryDuration = $duration, addMemoriesToLibrary = $addToLibrary WHERE id = $userID";

if ($result = $con->query($sql)) {
    echo "Successfully updated user settings!";
}
else {
    die("Error: Could not update settings for user.");
}

$apnsPayload = createPayloadWithActionCode(20000);
sendAPNSToUserID($con, $apnsPayload, $userID, $apnsToken);

$result->free();
$con->close();