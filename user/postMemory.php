<?php

use http\QueryString;

include "/home/music/public_html/api/auth/key.php";
include "/home/music/public_html/api/auth/functions.php";
include "/home/music/public_html/api/apns/functions.php";

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
    $title = str_replace("'", "''", $song->title);
    $title = str_replace("%26", "&", $title);
    $album = str_replace("'", "''", $song->album);
    $album = str_replace("%26", "&", $album);
    $artist = str_replace("'", "''", $song->artist);
    $artist = str_replace("%26", "&", $artist);

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
            print("Error: Could not add $song->title to database.");
        }
    }
}

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

$payload = stripcslashes(mysqli_real_escape_string($con, $_POST["payload"]));
$payloadArray = json_decode($payload);

$title = fix64bitString($payloadArray->title);
$description = fix64bitString($payloadArray->description);
$id = $payloadArray->id;
$isDynamic = boolval($payloadArray->isDynamic);


if ($isDynamic != 1) {
    $isDynamic = 0;
}

$songs = $payloadArray->songs;

$ids = processSongs($con, $songs);
$idsImploded = implode(" ", $ids);

//Create the memory.
$sql = "INSERT INTO memories (title, description, libraryIDs, userID, isDynamic, id) VALUES ('$title', '$description', '$idsImploded', $userID, $isDynamic, '$id')";

if ($result = $con->query($sql)) {
    print("Successfully posted memory.");

    $apnsPayload = createPayloadWithActionCode(10000);

    sendAPNSToUserID($con, $apnsPayload, $userID);
}
else {
    $sql = "UPDATE memories SET title = '$title', description = '$description', libraryIDs = '$idsImploded' WHERE id = '$id' AND userID = $userID";
    if ($result = $con->query($sql)) {
        print("Successfully updated memory.");
    }
    else {
        die("Error: Issue creating memory.");
    }
}