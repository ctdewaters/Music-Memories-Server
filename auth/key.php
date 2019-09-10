<?php

function verifyAPIKey() {
    if (isset($_GET["apiKey"])) {
        $apiKey = $_GET["apiKey"];
        if ($apiKey != "F610FHRMJTPL9NH1XPYFQDYRYSXCX9XA") {
            die("Error: Incorrect API Key");
        }
    }
    else {
        die("Error: No API Key supplied with request.");
    }
}

?>