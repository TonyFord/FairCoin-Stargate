<?php

include("log.php");


if( empty($_GET["order_id"]) || empty($_GET["pay"]) ){
  add_log("log/error.log","botc_notification.php - order_id or pay is empty");
  exit;
}

add_log("log/tx.log","botc_notification.php - ".$_GET["order_id"].",".$_GET["pay"].",init");


include("db_connect.php");


$order_id = $_GET["order_id"];
$pay = $_GET["pay"];

if( $pay != "in" && $pay != "out" ){
  add_log("log/error.log","botc_notification.php - pay is unequal in|out ->".$pay);
  exit;
}

$timeout = ($pay == "in") ? TIMEOUT_PAY_IN : TIMEOUT_PAY_OUT;
$retry   = ($pay == "in") ? TIMEOUT_PAY_IN_RETRY : TIMEOUT_PAY_OUT_RETRY;

$sql = "UPDATE gateway_fiat_IBAN_order SET botc_pay_trigger='".$pay."', botc_pay_".$pay."_timeout=".(time()+$timeout)." WHERE order_id='".$order_id."'";

if ($conn->query($sql) === TRUE) {

    // success
    add_log("log/tx.log","botc_notification.php - ".$order_id.",".$pay.",success");

} else {

    // error return false
    add_log("log/error.log","botc_notification.php - ".$order_id.",".$pay.",UPDATE failed ".$sql);

}

include("db_disconnect.php");

?>
