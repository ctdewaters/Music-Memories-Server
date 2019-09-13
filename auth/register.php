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
    die("ERROR: Identifying value (username/Apple ID) not received!");
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

if (isset($_GET['password'])) {
    $JWT = mysqli_real_escape_string($con, $_GET["password"]);
    $decodedJWT = decodeJWT($JWT);

    $iss = $decodedJWT->iss;
    $aud = $decodedJWT->aud;
    $sub = $decodedJWT->sub;

    echo "$JWT               ISS: $iss AUD: $aud  SUB: $sub";

    if ($iss != "https://appleid.apple.com" || $aud != "com.CollinDeWaters.Music-Memories" || $sub != $appleID) {
        die("Error: Invalid JWT token passed.");
    }

    $rawPassword = $iss . "%^#" . $sub . "@!*" . $aud;
    $passwordHash = password_hash($rawPassword, PASSWORD_DEFAULT);
}
else {
    die("Error: No password value received.");
}

$sql = "INSERT INTO users (firstName, lastName, password, appleID) VALUES ('$firstName', '$lastName', '$passwordHash', '$appleID')";

if($con->query($sql)) {
    $responseMessage = "Successfully registered user " . $firstName . " " . $lastName . "!";

    $sql = "SELECT * FROM users WHERE appleID = '$appleID'";

    $result = $con->query($sql);
    $response = createResponsePayload($result, $responseMessage);
}
else {
    $userID = verifyUser($con);

    $responseMessage = "Successfully verified user " . $firstName . " " . $lastName . "!";

    $sql = "SELECT * FROM users WHERE appleID = '$appleID'";
    $result = $con->query($sql);
    $response  = createResponsePayload($result, $responseMessage);
}

print(json_encode($response));

?>

