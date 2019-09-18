<?php

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

if (isset($_GET["memoryID"])) {
    $memoryID = mysqli_real_escape_string($con, $_GET["memoryID"]);
}
else {
    die("Error: No memory ID retrieved with request.");
}

$sql = "SELECT imageIDs, deletedImageIDs FROM memories WHERE id = '$memoryID' AND userID = $userID";

if($result = $con->query($sql)) {
    $row = $result->fetch_assoc();
    $imageIDs = $row["imageIDs"];
    $explodedImageIDs = explode(" ", $imageIDs);
    $remove = array("");
    $explodedImageIDs = array_diff($explodedImageIDs, $remove);
    $row["imageIDs"] = array_values($explodedImageIDs);

    $deletedImageIDs = $row["deletedImageIDs"];
    $explodedDeletedImageIDs = explode(" ", $deletedImageIDs);
    $explodedDeletedImageIDs = array_diff($explodedDeletedImageIDs, $remove);
    $row["deletedImageIDs"] = array_values($explodedDeletedImageIDs);

    echo json_encode($row);
}
else {
    echo "Error: Could not retrieve images for memory with id $memoryID.";
}

$con->close();