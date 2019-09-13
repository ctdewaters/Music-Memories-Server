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


$sql = "SELECT * FROM memories WHERE userID = $userID";

if ($result = $con->query($sql)) {
    $data = array();
    while ($row = $result->fetch_assoc()){
        $row["isDynamic"] = (bool)$row["isDynamic"];

        //Get songs.
        $songIDs = $row["songs"];
        $songs = array();
        foreach($songIDs as $id) {
            $sql = "SELECT title, artist, album FROM songs WHERE id = $id";
            if($result = $con->query($sql)) {
                $songs[] = $result->fetch_assoc();
            }
        }
        $row["songs"] = $songs;

        //Add the row to the data array.
        $data[] = $row;
    }
    echo json_encode($data);
}
else {
    die("Error: Could not retrieve memories for user.");
}