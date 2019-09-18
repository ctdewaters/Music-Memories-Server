<?php

include "/home/music/public_html/api/auth/key.php";
include "/home/music/public_html/api/auth/functions.php";
include "/home/music/public_html/api/apns/functions.php";


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
            if ($songIDsExploded = explode(" ", $songIDs)) {
                $songs = array();
                foreach($songIDsExploded as $id) {
                    $sql = "SELECT title, artist, album FROM songs WHERE id = $id";
                    if($songResult = $con->query($sql)) {
                        $songs[] = $songResult->fetch_assoc();
                    }
                }
                $row["songs"] = $songs;
            }
            else {
                $row["songs"] = [];
            }
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