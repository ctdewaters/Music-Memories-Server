<?php

function sendAPNSPush($http2ch, $payload, $token) {
    $jws = "eyJhbGciOiJFUzI1NiIsInR5cCI6IkpXVCIsImtpZCI6IldQWVgyWkZQVzYifQ.eyJpc3MiOiJBUDQ4U0NUM0oyIiwiaWF0IjoxNTY4MjQ1MTU3LCJleHAiOjE1ODM3OTcxNTd9.kWZYBlw6-uJhRnCnAXT4hCvp-F2xA89GExGLyn7Ioz_AyjBTuNLs86jfwMVFcQ3AFlQcPd4FYc86hXVwWMXH6Q";
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
    print_r($result);
    // get response
    $status = curl_getinfo($http2ch);
    return $status;
}

// this is only needed with php prior to 5.5.24
if (!defined('CURL_HTTP_VERSION_2_0')) {
    define('CURL_HTTP_VERSION_2_0', 3);
}

// open connection
$http2ch = curl_init();
curl_setopt($http2ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);

// send push
$token = "2070d564ef06c9ab48025621625abc0c89f9ee3bc3f6114c5c3681499493a74f";


$message = '{
    "aps" : {
        "alert" : {},
        "content-available" : 1
    },
    "acme1" : "I ACTUALLY MADE THIS WORK"
}';
sendAPNSPush($http2ch, $message, $token);
