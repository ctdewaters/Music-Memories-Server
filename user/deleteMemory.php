<?php

use http\QueryString;

include "/home/music/public_html/api/auth/key.php";
include "/home/music/public_html/api/auth/functions.php";
include "/home/music/public_html/api/apns/functions.php";

//MARK: - Post Memory
verifyAPIKey();

//Connect to the database
$con = mysqli_connect("localhost","music_music","Ferrari9488","music_memories");

// Check connection
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

$userID = verifyUser($con);

if (isset($_GET["id"])) {
    $id = mysqli_real_escape_string($con, $_GET["id"]);
}
else {
    die("Error: No memory ID retrieved with request.");
}

$sql = "INSERT INTO deletedMemories (id, title, description, libraryIDs, userID, isDynamic, startDate, endDate)
          SELECT *
          FROM memories
          WHERE id = '$id' AND userID = $userID";

if ($result = $con->query($sql)) {
}

$sql = "DELETE FROM memories WHERE id = '$id' AND userID = $userID";
if ($result = $con->query($sql)) {
    print("Successfully deleted memory $id.");
}
else {
    die("Error: Unable to delete memory $id");
}

$apnsPayload = createPayloadWithActionCode(999);
sendAPNSToUserID($con, $apnsPayload, $userID);

$result->free();
$con->close();