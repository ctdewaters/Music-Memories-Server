<?php
include_once "functions.php";
include "key.php";

verifyAPIKey();

//Connect to the database
$con = mysqli_connect("localhost","music_music","Ferrari9488","music_memories");

// Check connection
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

if (isset($_GET["appleID"])) {
    $appleID = mysqli_real_escape_string($con, $_GET['appleID']);
}
else {
    //Check for username.
    if (isset($_GET["username"])) {
        $username = mysqli_real_escape_string($con, $_GET['username']);
    }
    else {
        die("ERROR: Identifying value (username/Apple ID) not received!");
    }

}

if (isset($_GET['firstName'])) {
    $firstName = mysqli_real_escape_string($con, $_GET['firstName']);
}

if (isset($_GET['lastName'])) {
    $lastName = mysqli_real_escape_string($con, $_GET['lastName']);
}

$name = $firstName . ' ' . $lastName;

if($name === ' ') {
    die("Error: No name value received.");
}

if (isset($_GET['bio'])) {
    $bio = mysqli_real_escape_string($con, $_GET['bio']);
}

if (isset($_GET['password'])) {
    $passwordHash = mysqli_real_escape_string($con, password_hash($_GET['password'], PASSWORD_DEFAULT));
}
else {
    die("Error: No password value received.");
}

if(isset($username)) {
    $sql = "INSERT INTO users (firstName, lastName, bio, password, username) VALUES ('$firstName', '$lastName', '$bio', '$passwordHash', '$username')";
}
else {
    $sql = "INSERT INTO users (firstName, lastName, bio, password, appleID) VALUES ('$firstName', '$lastName', '$bio', '$passwordHash', '$appleID')";
}

if(mysqli_query($con, $sql)) {
    $responseMessage = "Successfully registered user " . $firstName . " " . $lastName . "!";
    $sql = "SELECT * FROM users WHERE username = '$username' OR appleID = '$appleID'";

    if(isset($username)) {
        $sql = "SELECT * FROM users WHERE username = '$username'";
    }
    else {
        $sql = "SELECT * FROM users WHERE appleID = '$appleID'";
    }

    $result = $con->query($sql);
    $response = createResponsePayload($result, $responseMessage);
}
else {
    $responseMessage = "Error: Unable to register, please try again later!";
    $payload = null;
    $response = array();
    $response["result"] = null;
    $response["message"] = $responseMessage;
}

print(json_encode($response));

?>

