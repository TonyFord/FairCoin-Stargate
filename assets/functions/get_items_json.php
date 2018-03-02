<?php

include("db_connect.php");
$order_id = empty( $_POST["order_id"] ) ? "" : $_POST["order_id"];

$sql = "SELECT * FROM gateway_fiat_IBAN_items".(( $order_id != "") ? " WHERE order_id='".$order_id."'" : "" );
$result = $conn->query($sql);

$A=array();

if ($result->num_rows > 0) {
    // output data of each row

    while($row = $result->fetch_assoc()) {
        $A[]=$row;
    }

    // convert numbers from string to float
    foreach ( $A as $i=>$v ){
      $A[$i]["amount"]=$v["amount"]*1.00;
      $A[$i]["state"]=$v["state"];
      $A[$i]["fair_amount"]=$v["fair_amount"]*1.00000000;
      $A[$i]["beneficiary"]=utf8_encode($A[$i]["beneficiary"]);
      $A[$i]["concept"]=utf8_encode($A[$i]["concept"]);
    }


}
echo json_encode($A);

include("db_disconnect.php");
?>
