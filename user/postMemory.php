<?php

use http\QueryString;

include "/home/music/public_html/api/auth/key.php";
include "/home/music/public_html/api/auth/functions.php";

//MARK: - Functions

///Adds songs to the songs table if necessary and returns an array of IDs.
function processSongs(mysqli $con, array $songs) {
    //Iterate through songs.

    $songIDs = array();
    foreach($songs as &$song) {
        $id = handleSong($con, $song);
        array_push($songIDs, $id);
    }
    return $songIDs;
}

function handleSong(mysqli $con, stdClass $song) {
    $title = $song->title;
    $album = $song->album;
    $artist = $song->artist;

    $sql = "SELECT * FROM songs WHERE title = '$title' AND album = '$album' AND artist = '$artist'";

    $result = $con->query($sql);

    if ($result->num_rows != 0) {
        //Retrieve the ID of the song.
        $row = $result->fetch_array();
        return $row["id"];
    }
    else {
        //Song not in table, add it and retrieve the generated ID.
        $sql = "INSERT INTO songs (artist, album, title) VALUES ('$artist', '$album', '$title')";

        if ($result = $con->query($sql)) {
            //Added to the database run the function again.
            return handleSong($con, $song);
        } else {
            die("Error: An error adding a song occurred.");
        }
    }
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

$payload = stripcslashes(mysqli_real_escape_string($con, $_GET["payload"]));
$payloadArray = json_decode($payload);

$title = $payloadArray->title;
$description = $payloadArray->description;
$id = $payloadArray->id;
$isDynamic = boolval($payloadArray->isDynamic);

$songs = $payloadArray->songs;

$ids = processSongs($con, $songs);
$idsImploded = implode(" ", $ids);

//Create the memory.
$sql = "INSERT INTO memories (title, description, libraryIDs, userID, isDynamic, id) VALUES ('$title', '$description', '$idsImploded', $userID, $isDynamic, '$id')";

if ($result = $con->query($sql)) {
    die("Successfully posted memory.");
}
else {
    $sql = "UPDATE memories SET title = '$title', description = '$description', libraryIDs = '$idsImploded' WHERE id = '$id' AND userID = $userID";
    if ($result = $con->query($sql)) {
        die("Successfully updated memory.");
    }
    else {
        die("Error: Issue creating memory.");
    }
}
