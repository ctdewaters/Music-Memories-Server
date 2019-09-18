<?php

function sendAPNSPush($jws, $http2ch, $payload, $token) {
    $http2_server = 'https://api.development.push.apple.com'; // or 'api.push.apple.com' if production
    $app_bundle_id = 'com.CollinDeWaters.Music-Memories';

    // url (endpoint)
    $url = "{$http2_server}/3/device/{$token}";
    // headers
    $headers = array(
        "apns-topic: {$app_bundle_id}",
        'Authorization: bearer ' . $jws
    );
    // other curl options
    curl_setopt_array($http2ch, array(
        CURLOPT_URL => $url,
        CURLOPT_PORT => 443,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POST => TRUE,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HEADER => 1
    ));
    // go...
    $result = curl_exec($http2ch);
    if ($result === FALSE) {
        print_r(curl_error($http2ch));
        throw new Exception("Curl failed: " . curl_error($http2ch));
    }
    // get response
    $status = curl_getinfo($http2ch);
    return $status;
}

function retrieveAPNSTokensForUserID(mysqli $con, $userID) {
    $sql = "SELECT * FROM users WHERE id = $userID";

    if ($result = $con->query($sql)) {
        if ($row = $result->fetch_array()) {
            $tokensImploded = $row["apnsTokens"];
            return explode(" ", $tokensImploded);
        }
    }
    return false;
}

function sendAPNSToUserID(mysqli $con, $payload, $userID) {
    //Setup the HTTP2 connection.
    if (!defined('CURL_HTTP_VERSION_2_0')) {
        define('CURL_HTTP_VERSION_2_0', 3);
    }

    // open connection
    $http2ch = curl_init();
    curl_setopt($http2ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);

    //Create the APNS provider token.
    $apnsCon = apnsCon();
    $jws = (string)retrieveProviderToken($apnsCon);
    $apnsCon->close();


    $userDeviceTokens = retrieveAPNSTokensForUserID($con, $userID);
    foreach ($userDeviceTokens as &$token) {
        $result = sendAPNSPush($jws, $http2ch, $payload, $token);
        $httpCode = $result["http_code"];

        print_r($httpCode . " ");

        if ($httpCode == 400 || $httpCode == 410) {
            //Invalid token, delete it from the user's account.
            deleteTokenFromUserID($con, $userID, $token);
        }
        elseif ($httpCode == 200) {
            //Successful request.
            echo "SUCCESSFUL REQUEST.";
        }
    }
}

function deleteTokenFromUserID(mysqli $con, $userID, $token) {
    $currentTokens = retrieveAPNSTokensForUserID($con, $userID);

    foreach (array_keys($currentTokens, $token) as $key) {
        unset($currentTokens[$key]);
    }

    $implodedTokens = implode(" ", $currentTokens);

    $sql = "UPDATE users SET apnsTokens = '$implodedTokens' WHERE id = $userID";

    if($con->query($sql)) {
    }
}

function createPayloadWithActionCode($actionCode) {
    $payload = '{
    "aps" : {
        "alert" : {},
        "content-available" : 1
    },
    "actionCode" : "' . $actionCode . '"
    }';
    return $payload;
}

function createImageUploadPayload($imageID, $memoryID, $actionCode) {
    $payload = '{
    "aps" : {
        "alert" : {},
        "content-available" : 1
    },
    "actionCode" : "' . $actionCode . '",
    "memoryID" : "' . $memoryID . '",
    "imageID" : "' . $imageID . '"
    }';
    return $payload;
}

function retrieveProviderToken($con) {
    $sql = "SELECT token FROM apnsToken LIMIT 1";

    $result = mysqli_query($con, $sql);

    $row = $result->fetch_assoc();

    return $row["token"];
}

function updateProviderToken($con) {

    $jwt = (string)exec("/usr/bin/python /home/music/public_html/api/apns/apns_token.py");

    $sql = "UPDATE apnsToken SET token = '$jwt' WHERE id = 1";

    $result = mysqli_query($con, $sql);
}

function apnsCon() {
    //Connect to the database
    $con = mysqli_connect("localhost","music_music","Ferrari9488","music_devtoken");

    // Check connection
    if (mysqli_connect_errno()) {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }

    return $con;
}