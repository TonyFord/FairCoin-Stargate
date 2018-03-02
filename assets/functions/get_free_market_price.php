<?php

//  https://api.coinmarketcap.com/v1/ticker/faircoin/?convert=EUR


$filename="../free_market_price.json";

$JSON=Array();
$upd=false;

if(file_exists($filename)){

  $fp=fopen($filename,"r");
  $data = fread($fp,filesize($filename));
  fclose($fp);

  $JSON=json_decode($data,true);
  //if( ( $JSON[0]["last_updated"] )*1 < time()-3600 ) $upd=true;
  if( filemtime( $filename ) < time()-3600 ) $upd=true;
}

if ( $JSON.length == 0 ) $upd=true;


if( $upd ) {

  $ch = curl_init();
  //curl_setopt($ch, CURLOPT_URL, 'https://api.coinmarketcap.com/v1/ticker/faircoin/?convert=EUR');
  curl_setopt($ch, CURLOPT_URL, 'https://api.chip-chap.com/exchange/v1/ticker/eur');
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $data = curl_exec($ch);
  curl_close($ch);

  $fp=fopen($filename,"w+");
  fwrite($fp,$data);
  fclose($fp);
  $JSON=json_decode($data);

}

echo json_encode( $JSON );

?>
