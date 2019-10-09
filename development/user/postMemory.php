<?php

use http\QueryString;

include "/home/music/public_html/api/development/auth/key.php";
include "/home/music/public_html/api/development/auth/functions.php";
include "/home/music/public_html/api/development/apns/functions.php";
include "functions.php";

//MARK: - Functions

function fix64bitString($str) {
    $str = str_replace(" ", "+", $str);
    $str = str_replace("\n", "+", $str);
    return $str;
}

//MARK: - Post Memory
verifyAPIKey();

//Connect to the database
$con = mysqli_connect("localhost","music_music","Ferrari9488","music_memories");

// Check connection
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

$userID = verifyUser($con);

$apns = mysqli_real_escape_string($con, $_GET["apns"]);
$apnsToken = mysqli_real_escape_string($con, $_GET["apnsToken"]);
$payload = stripcslashes(mysqli_real_escape_string($con, $_POST["payload"]));
$payloadArray = json_decode($payload);

$title = fix64bitString($payloadArray->title);
$description = fix64bitString($payloadArray->description);
$id = $payloadArray->id;
$isDynamic = boolval($payloadArray->isDynamic);

$startDate = fix64bitString($payloadArray->startDate);
$endDate = fix64bitString($payloadArray->endDate);

if ($isDynamic != 1) {
    $isDynamic = 0;
}

$songs = $payloadArray->songs;

$sql = "SELECT libraryIDs FROM memories WHERE id = '$id'";
if ($result = $con->query($sql)) {
    if ($result->num_rows > 0 ) {
        $row = $result->fetch_assoc();
        $libraryIDs = $row["libraryIDs"];
        $currentLibraryIDs = explode(" ", $libraryIDs);
    }
    else {
        $currentLibraryIDs = array();
    }
}

$payloadIDs = processSongs($con, $songs);

foreach($payloadIDs as $songID){
    if(!in_array($songID, $currentLibraryIDs, true)){
        array_push($currentLibraryIDs, $songID);
    }
}

$idsImploded = implode(" ", $currentLibraryIDs);

//Create the memory.
$sql = "INSERT INTO memories (title, description, libraryIDs, userID, isDynamic, id, startDate, endDate) VALUES ('$title', '$description', '$idsImploded', $userID, $isDynamic, '$id', '$startDate', '$endDate')";

if ($result = $con->query($sql)) {
    print("Successfully posted memory.");
}
else {
    $sql = "UPDATE memories SET title = '$title', description = '$description', libraryIDs = '$idsImploded', startDate = '$startDate', endDate = '$endDate' WHERE id = '$id' AND userID = $userID";
    if ($result = $con->query($sql)) {
        print("Successfully updated memory.");
    }
    else {
        die("Error: Issue creating memory.");
    }
}

if ($apns == "true") {
    $apnsPayload = createPayloadWithActionCode(10000);
    sendAPNSToUserID($con, $apnsPayload, $userID, $apnsToken);
}

$result->free();
$con->close();