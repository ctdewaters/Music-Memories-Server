<?php

include "/home/music/public_html/api/development/auth/key.php";
include "/home/music/public_html/api/development/auth/functions.php";
include "/home/music/public_html/api/development/apns/functions.php";
include "functions.php";

//MARK: - Post Memory
verifyAPIKey();

//Connect to the database
$con = mysqli_connect("localhost","music_music","Ferrari9488","music_memories");

// Check connection
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

$userID = verifyUser($con);

$title = urldecode(mysqli_real_escape_string($con, $_GET["title"]));
$artist = urldecode(mysqli_real_escape_string($con, $_GET["artist"]));
$album = urldecode(mysqli_real_escape_string($con, $_GET["album"]));
$memoryID = mysqli_real_escape_string($con, $_GET["memoryID"]);

$stdClassObject = new stdClass();
$stdClassObject->title = $title;
$stdClassObject->artist = $artist;
$stdClassObject->album = $album;

$libraryID = handleSongWithoutApostropheReplacement($con, $stdClassObject);

$sql = "SELECT libraryIDs FROM memories WHERE id = '$memoryID'";
if ($result = $con->query($sql)) {
    if ($result->num_rows > 0 ) {
        $row = $result->fetch_assoc();
        $libraryIDs = $row["libraryIDs"];
        $currentLibraryIDs = explode(" ", $libraryIDs);
    }
    else {
        die("Error: No id's to delete for memory $memoryID.");
    }
}

$remove = array($libraryID, "");

$currentLibraryIDs = array_diff($currentLibraryIDs, $remove);
$implodedIDs = implode(" ", $currentLibraryIDs);
$sql = "UPDATE memories SET libraryIDs = '$implodedIDs' WHERE id = '$memoryID' AND userID = $userID";

if ($result = $con->query($sql)) {
    print("Successfully deleted song from memory $memoryID.");
}
else {
    die("Error: Issue deleting song for memory $memoryID.");
}

$apnsPayload = createPayloadWithActionCode(10000);
sendAPNSToUserID($con, $apnsPayload, $userID);

$con->close();
