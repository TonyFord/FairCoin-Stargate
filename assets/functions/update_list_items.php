<?php
include("db_connect.php");

if( empty($_POST["c"]) || empty($_POST["order_id"]) ){
  exit;
}

$oneCode=$_POST["c"];
$order_id=$_POST["order_id"];
$state=$_POST["state"];

require_once 'GoogleAuthenticator.php';

$ga = new PHPGangsta_GoogleAuthenticator();

$checkResult = $ga->verifyCode($secret, $oneCode, 2);    // 2 = 2*30sec clock tolerance
if ($checkResult) {
  $sql = "UPDATE gateway_fiat_IBAN_items SET state=".($state+1)." WHERE order_id='".$order_id."' AND state=".$state;
  $result = $conn->query($sql);
  echo "OK";
} else {
  echo 'FAILED';
}

include("db_disconnect.php");
?>
