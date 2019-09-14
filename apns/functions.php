<?php

function sendAPNSPush($http2ch, $payload, $token) {
    $jws = "eyJhbGciOiJFUzI1NiIsInR5cCI6IkpXVCIsImtpZCI6IldQWVgyWkZQVzYifQ.eyJpc3MiOiJBUDQ4U0NUM0oyIiwiaWF0IjoxNTY4NDIwMDU2LCJleHAiOjE1ODM5NzIwNTZ9.O-aD9TWsA0Pz042Re4MX2u6xkWpyxylIbmurv4TI-wD2EGjnzlZLaq_8AMm5FCW-mSZonN4fQMd3JJvBJxKiSg";
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
        throw new Exception("Curl failed: " . curl_error($http2ch));
    }
    // get response
    print_r($result);
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


    $userDeviceTokens = retrieveAPNSTokensForUserID($con, $userID);
    foreach ($userDeviceTokens as &$token) {
        print_r("\n\n\n\n\n\n\n\n\n\n");
        $result = sendAPNSPush($http2ch, $payload, $token);
        $httpCode = $result["http_code"];

        if ($httpCode == 400 || $httpCode == 410) {
            //Invalid token, delete it from the user's account.
            deleteTokenFromUserID($con, $userID, $token);
        }
        elseif ($httpCode == 200) {
            //Successful request.
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


/*
$message = '{
    "aps" : {
        "alert" : {},
        "content-available" : 1
    },
    "acme1" : "I ACTUALLY MADE THIS WORK"
}';
sendAPNSPush($http2ch, $message, $token);
*/