<?php

include "/home/music/public_html/api/development/auth/key.php";
include "/home/music/public_html/api/development/auth/functions.php";
include "/home/music/public_html/api/development/apns/functions.php";


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

$sql = "  INSERT INTO memories (id, title, description, libraryIDs, deletedLibraryIDs, userID, isDynamic, startDate, endDate, imageIDs, deletedImageIDs)
          SELECT id, title, description, libraryIDs, deletedLibraryIDs, userID, isDynamic, startDate, endDate, imageIDs, deletedImageIDs
          FROM deletedMemories
          WHERE id = '$id' AND userID = $userID";

if ($result = $con->query($sql)) {
}
else {
    print("Error: Unable to restore memory $id");
}

$sql = "DELETE FROM deletedMemories WHERE id = '$id' AND userID = $userID";
if ($result = $con->query($sql)) {
    print("Successfully restored memory $id.");
}
else {
    die("Error: Unable to restore memory $id");
}

$apnsPayload = createPayloadWithActionCode(777);
sendAPNSToUserID($con, $apnsPayload, $userID);

$result->free();
$con->close();