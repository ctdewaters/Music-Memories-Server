<?php

include "/home/music/public_html/api/apns/functions.php";

//Connect to the database
$con = apnsCon();

updateProviderToken($con);

echo "PROVIDER TOKEN: " . retrieveProviderToken($con);

$con->close();