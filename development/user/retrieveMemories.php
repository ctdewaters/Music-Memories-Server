<?php

include "/home/music/public_html/api/development/auth/key.php";
include "/home/music/public_html/api/development/auth/functions.php";
include "/home/music/public_html/api/development/apns/functions.php";
include "functions.php";


verifyAPIKey();

//Connect to the database
$con = mysqli_connect("localhost","music_music","Ferrari9488","music_memories");

// Check connection
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

$userID = verifyUser($con);


$sql = "SELECT id, title, description, libraryIDs, userID, isDynamic, startDate, endDate FROM memories WHERE userID = $userID";

if ($result = $con->query($sql)) {
    $data = array();
    while ($row = $result->fetch_assoc()){
        $row["isDynamic"] = (bool)$row["isDynamic"];

        //Get songs.
        if ($songIDs = $row["libraryIDs"]) {
            $row["songs"] = libraryIDsToSongs($con, $songIDs);
        }
        else {
            $row["songs"] = [];
        }


        //Add the row to the data array.
        $data[] = $row;
    }
}
else {
    die("Error: Could not retrieve memories for user.");
}

echo json_encode($data);

$result->free();
$con->close();