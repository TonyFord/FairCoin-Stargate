<?php

$APICLIENT = [
  "grant_type"=> "password",
  "username"  => "myUsername",
  "password"  => "myPassword",
  "client_id" => "47_3eb93......",
  "client_secret" => "f0qkwzms....."
];

$tx_expired=900;          // seconds for receiving tx
$tx_confirmations=1;      // confirmations
$tx_url_notification="http://fairplayground.info/FairCoin-Stargate/assets/functions/botc_notification.php";  // notification url for further events

  define("TIMEOUT_PAY_IN",900);
  define("TIMEOUT_PAY_IN_RETRY",1);

  define("TIMEOUT_PAY_OUT",43200);
  define("TIMEOUT_PAY_OUT_RETRY",6);


?>
