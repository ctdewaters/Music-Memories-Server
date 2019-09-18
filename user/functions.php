<?php

function libraryIDsToSongs(mysqli $con, $libraryIDs) {
    if ($songIDsExploded = explode(" ", $libraryIDs)) {
        $songs = array();
        foreach($songIDsExploded as $id) {
            $sql = "SELECT title, artist, album FROM songs WHERE id = $id";
            if($songResult = $con->query($sql)) {
                $songs[] = $songResult->fetch_assoc();
            }
        }
        return $songs;
    }
    else {
        return [];
    }
}

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

    echo $title . "          ";

    $sql = stripcslashes(mysqli_real_escape_string($con, "SELECT * FROM songs WHERE title = '$title' AND album = '$album' AND artist = '$artist'"));

    echo $sql . "                ";

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
            //Added to the database, run the function again.
            return handleSong($con, $song);
        } else {
            print("Error: Could not add $song->title to database.");
        }
    }
}

function handleSongWithoutApostropheReplacement(mysqli $con, stdClass $song) {
    $title = $song->title;
    $title = str_replace("%26", "&", $title);
    $album = $song->album;
    $album = str_replace("%26", "&", $album);
    $artist = $song->artist;
    $artist = str_replace("%26", "&", $artist);

    echo $title . "          ";

    $sql = stripcslashes(mysqli_real_escape_string($con, "SELECT * FROM songs WHERE title = '$title' AND album = '$album' AND artist = '$artist'"));

    echo $sql . "                ";

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
            //Added to the database, run the function again.
            return handleSong($con, $song);
        } else {
            print("Error: Could not add $song->title to database.");
        }
    }
}