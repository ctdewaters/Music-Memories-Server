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

?>