<?php

include "/home/music/public_html/api/auth/key.php";
include "/home/music/public_html/api/auth/functions.php";
include "/home/music/public_html/api/apns/functions.php";

function addImageToMemory(mysqli $con, $memoryID, $imageID, $userID) {
    $sql = "SELECT imageIDs FROM memories WHERE id = '$memoryID' AND userID = $userID";
    if ($result = $con->query($sql)) {
        if ($row = $result->fetch_assoc()) {
            $currentIDs = $row["imageIDs"];
            $currentIDsExploded = explode(" ", $currentIDs);
            $currentIDsExploded[] = $imageID;

            $idsImploded = implode(" ", $currentIDsExploded);

            $sql = "UPDATE memories SET imageIDs = '$idsImploded' WHERE id = '$memoryID' AND userID = $userID";
            $con->query($sql);
        }
    }
    else {
        print("Error: Could not update image ids list for memory '$memoryID'.");
    }
}

verifyAPIKey();

//Connect to the database
$con = mysqli_connect("localhost","music_music","Ferrari9488","music_memories");

// Check connection
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

$userID = verifyUser($con);

$memoryID = mysqli_real_escape_string($con, $_GET["memoryID"]);
$imageID = mysqli_real_escape_string($con, $_GET["imageID"]);

$target_dir = "/home/music/public_html/memories/images";
if(!file_exists($target_dir))  {
    mkdir($target_dir, 0777, true);
}

$target_dir = $target_dir . "/" . basename($_FILES["file"]["name"]);

if (!file_exists($target_dir)) {
    if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_dir)) {
        print("Successfully uploaded file.");

        addImageToMemory($con, $memoryID, $imageID, $userID);
    }
    else {
        die("Error: Could not save image to server.");
    }
}
else {
    die("Error: File already exists in server!");
}

$con->close();