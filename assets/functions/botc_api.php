<?php

include("botc_connect.php");

/**
 *
 */
class BotC
{
  private $auth_token;
  public $balances;


  function __construct() {

    $this->auth();

  }

  function auth(){

    $J = json_decode( $this->request('https://api.bankofthecommons.coop/oauth/v2/token', true, $GLOBALS["APICLIENT"]),true );
    if( isset( $J["access_token"] )) {
      $this->auth_token = $J["access_token"];
    } else {
      $this->auth_token = false;
    }

  }

  function isConnected(){
    return ( $this->auth_token == false ) ? false : true;
  }

  function request($url, $post, $param){

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url );
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    "Authorization: Bearer ".$this->auth_token
    ));
    if( $post == true ){
      curl_setopt($ch, CURLOPT_POST,true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $param );
    }

    $data = curl_exec($ch);
    curl_close($ch);

    return $data;

  }

  function getBalances(){
    $J = json_decode( $this->request('https://api.bankofthecommons.coop/user/v1/wallet', false, ""),true );
    foreach( $J["data"] as $j ){
      if( $j["status"] == "enabled" ) $this->balances[ $j["currency"] ] = $j["available"] / pow( 10, $j["scale"]);
    }
  }

  function getTxs(){
    $J = json_decode( $this->request('https://api.bankofthecommons.coop/user/v1/wallet/transactions', false, ""),true );
    return $J;
  }

  function getTx($botc_tx_id,$tx_type){

    $J = json_decode( $this->request('https://api.bankofthecommons.coop/methods/v1/'.$tx_type.'/'.$botc_tx_id, false, ""),true );
    return $J;

  }

  function receive_FAIR( $order_id, $amount, $concept ){

    $param=array(
      "currency" => "fac",
      "amount" => number_format( $amount * 100000000,0,'','')*1,
      "confirmations" => $GLOBALS["tx_confirmations"],
      "expires_in" => $GLOBALS["tx_expired"],
      "concept" => $concept,
      "url_notification" => $GLOBALS["tx_url_notification"]."?order_id=".$order_id."&pay=in"
    );

    return json_decode( $this->request('https://api.bankofthecommons.coop/methods/v1/in/fac', true, $param ),true );

  }

  function send_SEPA( $order_id ){

    global $conn;

    //curl --data "access_token=ZDBlZjk4MmMzZjUyMDdmYjMwY2EyMzhjNjZmMjc0ZjAyMmQ4NzcyMDk1ZDI0MDRjZmY4NjE4OGIzYzhmY2RlOQ" --data "currency=EUR" --data "amount=100" --data "beneficiary=Xarxa Autogestió Social SCCL" --data "iban=ES4314910001242086855729" --data "bic_swift=TRIOESMMXXX" --data "concept=deposit B8M56" "https://api.bankofthecommons.coop/methods/v1/out/sepa"

    // get beneficiary, iban, bic by order_id
    $sql = "SELECT * FROM gateway_fiat_IBAN_order WHERE order_id='".$order_id."'";
    $result = $conn->query($sql);

    if( $row = $result->fetch_assoc()) {
        $param=array(
          "currency" => "EUR",
          "amount" => number_format( $row["amount"]*100,0,'',''),
          "beneficiary" => utf8_encode( $row["beneficiary"] ),
          "iban" => $row["IBAN"],
          "bic_swift" => $row["BIC"],
          "concept" => $row["concept"],
          "url_notification" => $GLOBALS["tx_url_notification"]."?order_id=".$order_id."&pay=out"
        );
        return json_decode( $this->request('https://api.bankofthecommons.coop/methods/v1/out/sepa', true, $param ),true );

    } else {
      return "not found";
    }

  }

}



?>
