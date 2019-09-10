<?php

include "/home/music/public_html/api/auth/key.php";

//Connect to the database
$con = mysqli_connect("localhost","music_music","Ferrari9488","music_devtoken");

// Check connection
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

$query = "SELECT * FROM developerToken LIMIT 1";

$result = mysqli_query($con, $query);

if ($result->num_rows == 0) {
    die("Error: no developer token found.");
}

$row = $result->fetch_assoc();

echo $row["developerToken"];

?>
