<?php

include "/home/music/public_html/api/auth/key.php";
include "/home/music/public_html/api/auth/functions.php";
include_once "functions.php";

//MARK: - Post Memory
verifyAPIKey();

//Connect to the database
$con = mysqli_connect("localhost","music_music","Ferrari9488","music_memories");

// Check connection
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

$userID = verifyUser($con);

if (isset($_GET["deviceToken"])) {
    $deviceToken = mysqli_real_escape_string($con, $_GET["deviceToken"]);
}
else {
    die("Error: No device token supplied with request.");
}

$currentTokens = retrieveAPNSTokensForUserID($con, $userID);

if (in_array($deviceToken, $currentTokens) === false) {
    array_push($currentTokens, $deviceToken);
}
else {
    die("Error: Device token already added to account.");
}

$implodedTokens = implode(" ", $currentTokens);

$sql = "UPDATE users SET apnsTokens = '$implodedTokens' WHERE id = $userID";

if ($result = $con->query($sql)) {
    die("Successfully updated APNS tokens!");
}
else {
    die("Error: Unable to update APNS tokens.");
}