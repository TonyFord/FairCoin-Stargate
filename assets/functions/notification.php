<?php

include("log.php");
include("db_connect.php");
include("botc_api.php");


if( ! empty($_GET["order_id"]) ){

  // trigger check
  $order_id=$_GET["order_id"];
  add_log("log/tx.log","notification.php - order_id ".substr($order_id,0,2)."....,triggered check");
  echo check_tx($conn, $order_id);

} else {

  // timeout check

  $sql = "UPDATE gateway_fiat_IBAN_order SET botc_pay_in_timeout=0 WHERE botc_pay_in_status = 'success'";
  if( $conn->query($sql) !== TRUE ){
    add_log("log/error.log","notification.php - UPDATE1 failed ".$sql);
  }

  $sql = "UPDATE gateway_fiat_IBAN_order SET botc_pay_out_timeout=0 WHERE botc_pay_out_status = 'success'";
  if( $conn->query($sql) !== TRUE ){
    add_log("log/error.log","notification.php - UPDATE1 failed ".$sql);
  }

  $sql = "UPDATE gateway_fiat_IBAN_order SET botc_pay_trigger='in', botc_pay_in_timeout=1 WHERE botc_pay_in_status != 'success' AND botc_pay_in_timeout<".time()." AND botc_pay_in_timeout>1";
  if( $conn->query($sql) !== TRUE ){
    add_log("log/error.log","notification.php - UPDATE1 failed ".$sql);
  }


  $sql = "UPDATE gateway_fiat_IBAN_order SET botc_pay_trigger='out', botc_pay_out_timeout=1 WHERE botc_pay_out_status != 'success' AND botc_pay_out_timeout<".time()." AND botc_pay_out_timeout>1";
  if( $conn->query($sql) !== TRUE ){
    add_log("log/error.log","notification.php - UPDATE2 failed ".$sql);
  }

  $sql = "SELECT * FROM gateway_fiat_IBAN_order WHERE ( botc_pay_trigger = 'in' AND botc_pay_in_timeout = 1 ) OR ( botc_pay_trigger = 'out' AND botc_pay_out_timeout = 1 ) ";
  $result = $conn->query($sql);

  if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
      check_tx( $conn, $row["order_id"] );
    }
  }

}


function check_tx( $conn, $order_id ){

  $sql = "SELECT * FROM gateway_fiat_IBAN_order WHERE order_id='".$order_id."'";
  $result = $conn->query($sql);

  if ($result->num_rows > 0) {
    if($row = $result->fetch_assoc()) {

      if( $row["botc_pay_trigger"] == "in" || $row["botc_pay_trigger"] == "out" ){

        botc_update_tx_data($conn, $row, false);

      }

    }

  }

  return $row["status"];

}


function botc_update_tx_data($conn, $row, $timeout){

  add_log("log/tx.log","notification.php - botc_update_tx_data ".substr($row["order_id"],0,2)."....,start");

  $botc_tx_id=( $row["botc_pay_trigger"] == "in" ) ? $row["botc_pay_in_id"] : $row["botc_pay_out_id"];

  $botc = new BotC;
  $TX=$botc->getTx($botc_tx_id,$row["botc_pay_trigger"]."/".(( $row["botc_pay_trigger"] == "in" ) ? "fac" : "sepa" ) );

  $status="??"; $sqlopt="";

  if( $row["botc_pay_trigger"] == "in" ){

    if( $TX["status"] == "created" ){
      $status="00";
    }

    if( $TX["status"] == "received" ){
      $status="01";
    }

    if( $TX["status"] == "success" ){
      $status="02";
    }

    if( $row["status"] == $status ){

      add_log("log/tx.log","notification.php - botc_update_tx_data - ".substr($row["order_id"],0,2).".... - ".$row["botc_pay_trigger"].",".$status.", no changes and set retry");
      increase_retry($conn, $row);

    } else {

      if( $status == "02" ){

        // pay in done -> create pay out
        add_log("log/tx.log","notification.php - botc_update_tx_data - preSEPA ".$row["order_id"]."|".$row["amount"]."|".$row["concept"]);
        $PAY_OUT=$botc->send_SEPA( $row["order_id"] );
        add_log("log/tx.log","notification.php - botc_update_tx_data - postSEPA ".$PAY_OUT."|".json_encode($PAY_OUT));

        if( $PAY_OUT["status"] == "sending" ){
          $status="03";
        } else {
          $status="E02";
          add_log("log/error.log","notification.php - botc_update_tx_data - ".substr($row["order_id"],0,2).".... - ".$row["botc_pay_trigger"].",".$status." ".json_encode( $PAY_OUT ).", error ");
        }

        $sqlopt=", botc_pay_out_status='".$PAY_OUT["status"]."', botc_pay_out_id='".$PAY_OUT["id"]."'";

      }

    }

  } else if( $row["botc_pay_trigger"] == "out" ){

    if( $TX["status"] == "sending" ){
      $status="03";
    }

    if( $TX["status"] == "success" ){
      $status="04";
    }

    if( $row["status"] == $status ){

      add_log("log/tx.log","notification.php - botc_update_tx_data - ".substr($row["order_id"],0,2).".... - ".$row["botc_pay_trigger"].",".$status.",no changes and set retry");
      increase_retry($conn, $row);

    }

  }

  // update order status
  $sql = "UPDATE gateway_fiat_IBAN_order SET botc_pay_trigger='', botc_pay_".$row["botc_pay_trigger"]."_status='".$TX["status"]."', botc_pay_".$row["botc_pay_trigger"]."_timestamp=".time().$sqlopt.", status='".$status."' WHERE order_id='".$row["order_id"]."'";

  if ($conn->query($sql) === TRUE) {
      // success
      add_log("log/tx.log","notification.php - botc_update_tx_data - ".substr($row["order_id"],0,2).".... - ".$row["botc_pay_trigger"].",".$status.",success |".$botc_tx_id."|".json_encode($TX));
  } else {
      // error return false
      add_log("log/error.log","notification.php - botc_update_tx_data - ".substr($row["order_id"],0,2).".... - ".$row["botc_pay_trigger"].",".$status.",error");
  }
}


function increase_retry($conn, $row){

  $timeout = ($row["btc_pay_trigger"] == "in") ? TIMEOUT_PAY_IN : TIMEOUT_PAY_OUT;
  $retry   = ($row["btc_pay_trigger"] == "in") ? TIMEOUT_PAY_IN_RETRY : TIMEOUT_PAY_OUT_RETRY;

  if( $row["botc_pay_retry"] >= $retry ){
    // retry count reached -> set status error

    $sql = "UPDATE gateway_fiat_IBAN_order SET status='E".$row["status"]."' WHERE order_id='".$row["order_id"]."'";
  } else {
    // increase retry count and set timeout again
    $sql = "UPDATE gateway_fiat_IBAN_order SET botc_pay_".$row["botc_pay_trigger"]."_timeout=".(time()+$timeout).", botc_pay_retry=botc_pay_retry+1 WHERE order_id='".$row["order_id"]."'";
  }

  if ($conn->query($sql) === TRUE) {

    return true;

  } else {

    add_log("log/error.log","notification.php - UPDATE3 failed ".$sql);

    return false;
  }

}

include("db_disconnect.php");

?>
