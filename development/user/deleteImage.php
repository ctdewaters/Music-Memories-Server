<?php

use http\QueryString;

include "/home/music/public_html/api/development/auth/key.php";
include "/home/music/public_html/api/development/auth/functions.php";
include "/home/music/public_html/api/development/apns/functions.php";

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

if (isset($_GET["imageID"])) {
    $imageID = mysqli_real_escape_string($con, $_GET["imageID"]);
}
else {
    die("Error: No image ID retrieved with request.");
}

$filePath = "/home/music/public_html/memories/images/$imageID.mkimage";
if (file_exists($filePath)) {
    unlink($filePath);

    $sql = "SELECT imageIDs, deletedImageIDs FROM memories WHERE id = '$memoryID'";
    if ($result = $con->query($sql)) {
        $row = $result->fetch_assoc();

        //Active ids
        $currentIDs = $row["imageIDs"];
        $currentIDsExploded = explode(" ", $currentIDs);

        $remove = array($imageID, "");
        $newIDs = array_diff($currentIDsExploded, $remove);
        $newIDsImploded = implode(" ", $newIDs);

        //Deleted IDs.
        $currentDeletedIDs = $row["deletedImageIDs"];
        $currentDeletedIDsExploded = explode(" ", $currentDeletedIDs);
        $currentDeletedIDsExploded[] = $imageID;
        $newDeletedIDsImploded = implode(" ", $currentDeletedIDsExploded);

        $sql = "UPDATE memories SET imageIDs = '$newIDsImploded', deletedImageIDs = '$newDeletedIDsImploded' WHERE id = '$memoryID'";
        $con->query($sql);

        $payload = createImageUploadPayload($imageID, $memoryID, 512);
        sendAPNSToUserID($con, $payload, $userID);

        echo "Successfully deleted image.";
    }
    else {
        echo "Error: Unable to update table.";
    }
}
else {
    echo "No file found for image id $imageID.";
}

$con->close();