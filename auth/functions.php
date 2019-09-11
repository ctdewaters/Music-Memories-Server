<?php

function createResponsePayload(mysqli_result $result, $message) {
    $row = $result->fetch_row();
    $response = array();
    $user = array();
    $user["user"] = $row;
    $response["result"] = $user;
    $response["message"] = $message;
    return $response;
}

function verifyUser(mysqli $con) {
    if (isset($_GET["appleID"])) {
        $appleID = mysqli_real_escape_string($con, $_GET["appleID"]);
    }
    else {
        die("Error: No Apple ID value received.");
    }
    if (isset($_GET['password'])) {
        $password = mysqli_real_escape_string($con, $_GET['password']);
    }
    else {
        die("Error: No password value received.");
    }


    $sql = "SELECT * FROM users WHERE appleID = '$appleID'";
    $result = $con->query($sql);

    if ($row = $result->fetch_array()) {
        $storedPasswordHash = $row["password"];
        if (!password_verify($password, $storedPasswordHash)) {
            die("Error: Incorrect password!");
        }
    }
    else {
        die("Error: No user with ID : " . $appleID . ".");
    }
}
