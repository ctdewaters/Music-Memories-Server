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


$sql = "SELECT * FROM deletedMemories WHERE userID = $userID";

if ($result = $con->query($sql)) {
    $data = array();
    while ($row = $result->fetch_assoc()){
        //Add the row to the data array.
        $data[] = $row["id"];
    }
}
else {
    die("Error: Could not retrieve deleted memories for user.");
}

echo json_encode($data);


$result->free();
$con->close();