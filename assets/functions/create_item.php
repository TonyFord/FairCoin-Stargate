<?php

include("log.php");
include("token.php");
include("db_connect.php");
include("botc_api.php");

if( empty($_POST["iban"]) || empty($_POST["amount"]) || empty($_POST["concept"]) || empty($_POST["action"]) ){
  add_log("log/error.log","create_item.php - not all params posted ".
  "iban:".empty($_POST["iban"]).
  "amount:".empty($_POST["amount"]).
  "concept:".empty($_POST["concept"]).
  "action:".empty($_POST["action"]));
  exit;
}

$action   = $_POST["action"];
$iban     = $_POST["iban"];
$amount   = $_POST["amount"];
$concept  = $_POST["concept"];

$LIMITS=Array(50,100,200,5000);

$FAIR_OFFICIAL_PRICE=1.2;
$FAIR_FREE_MARKET=0;
$A=array();


if( $action == "calculate" ){

  $sql = "SELECT * FROM gateway_fiat_IBAN WHERE IBAN='".$iban."'";
  $result = $conn->query($sql);

  if ($result->num_rows > 0) {
    // output data of each row
    $score=0;
    if($row = $result->fetch_assoc()) {
        if( $row["popular"] == true ) $score++;
        if( $row["fair"] == true ) $score++;
        if( $row["faircoop"] == true ) $score++;
    }
    $A["limit"] = $LIMITS[$score];
    if( $row["fair"] == true ){
      $A["exchange_price"] = $FAIR_OFFICIAL_PRICE;
    } else {
      
    }
    $A["fair_amount"] = number_format( $amount / $A["exchange_price"],8,'.','' );
    echo json_encode($A);
  } else {
    echo "false";
  }


} else if( $action == "open" ){


  $sql = "SELECT * FROM gateway_fiat_IBAN WHERE IBAN='".$iban."'";
  $result = $conn->query($sql);

  if ($result->num_rows > 0) {
    // output data of each row

    $score=0;
    if($row = $result->fetch_assoc()) {
        if( $row["popular"] == true ) $score++;
        if( $row["fair"] == true ) $score++;
        if( $row["faircoop"] == true ) $score++;
    }
    $A["limit"] = $LIMITS[$score];
    if( $row["fair"] == true ) $A["exchange_price"] = $FAIR_OFFICIAL_PRICE;
    $A["fair_amount"] = number_format( $amount / $A["exchange_price"],8,'.','' );


    // create tx in mysql db
    $order_id=getToken(6);
    $A["order_id"]=$order_id;

    $botc=new BotC;

    $PAY_IN=$botc->receive_FAIR( $order_id, $A["fair_amount"], $order_id );

    $botc_pay_in_id = $PAY_IN["id"];
    $botc_pay_in_status = $PAY_IN["status"];
    $A["fair_address"] = $PAY_IN["pay_in_info"]["address"];

    add_log("log/tx.log","create_item.php - open stargate - ".substr($order_id,0,2));

    $sql = "INSERT INTO gateway_fiat_IBAN_order (order_id, beneficiary, IBAN, BIC, amount, concept, fair_address, fair_amount, status, botc_pay_in_id, botc_pay_in_status, botc_pay_in_timestamp, botc_pay_in_timeout)
    VALUES ('".$order_id."', '".$row["beneficiary"]."','".$row["IBAN"]."','".$row["BIC"]."', ".$amount.", '".$concept."', '".$A["fair_address"]."', ".$A["fair_amount"].",'00','".$botc_pay_in_id."','".$botc_pay_in_status."',".time().",".(time()+TIMEOUT_PAY_IN)." )";

    if ($conn->query($sql) === TRUE) {
        // success
        add_log("log/tx.log","create_item.php - INSERT done - ".substr($order_id,0,2));

        echo json_encode($A);
    } else {
        // error return false
        add_log("log/error.log","create_item.php - INSERT failed - ".$sql);
        echo "false";
    }



  } else {
    echo "false";
  }

}

include("db_disconnect.php");

?>
