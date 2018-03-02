<?php
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, "http://fairplayground.info/FairCoin-Stargate/assets/functions/notification.php" );
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

  $data = curl_exec($ch);
  curl_close($ch);
  echo $data;
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <title>FairCoin Stargate
    </title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="robots" content="noindex, nofollow">
    <link href="assets/css/parallax.css" rel="stylesheet">

    <link href="assets/css/demo.css" rel="stylesheet">
    <link href="assets/css/component.css" rel="stylesheet">
    <link href='https://fonts.googleapis.com/css?family=Raleway:200,400,800' rel='stylesheet' type='text/css'>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <link href="assets/css/bootswatch_solar.css" rel="stylesheet">
    <link href="assets/css/stargate.css" rel="stylesheet">

  </head>
  <body>

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>




    <link href='https://fonts.googleapis.com/css?family=Lato:300,400,700' rel='stylesheet' type='text/css'>

    <div id='title'>
      <span>
        FairCoin Stargate
      </span>
      <br>
      <span>
        Gateways
      </span>
      <br>
      <span class="gateway_list">
        ( under construction )
      </span>
    </div>




    <div class="container-fluid">
      <div id='stars'></div>
      <div id='stars2'></div>
      <div id='stars3'></div>
      <div class="demo-1">
        <div class="content">
      		<div id="large-header" class="large-header" style="height: 100%;">
      			<canvas id="demo-canvas" width="1323" height="402"></canvas>
      		</div>
      	</div>
      </div>

      <div class="row line1">
        <div class="col">
          <div class="input-group">
            <input id="gateway_id" class="form-control" type="text" value="" onkeyup="search_stargate(this)" placeholder="enter IBAN or stargate order id" tabindex="1">
            <p></p>
            <div class="invalid-feedback">
              <span id="gateway_id_invalid">no result</span>
            </div>
          </div>
        </div>
      </div>

      <div class="row line2 d-none">

        <div class="col-lg-2 col-sm-6">
          <input id="gateway" class="form-control d-none" type="text" value="" readonly>
        </div>
        <div class="col-lg col-sm-6">
          <input id="gateway_beneficiary" class="form-control d-none" type="text" value="" readonly>
        </div>
        <div class="col-lg">
          <input id="gateway_address" class="form-control d-none" type="text" value="" readonly>
        </div>

        <div id="gateway_rating" class="col-lg">
          <i id="t1" class="material-icons" data-toggle="tooltip" data-placement="top" title="">star</i>
          <i id="t2" class="material-icons" data-toggle="tooltip" data-placement="top" title="">star</i>
          <i id="t3" class="material-icons" data-toggle="tooltip" data-placement="top" title="">star</i>
          <p>max <span id="payment_limit"></span>&euro;<br>
          <span id="exchange_price"></span>&euro; / FAIR
          </p>
        </div>

      </div>

      <div class="row line3 d-none">

        <div class="col-lg">
          <div class="input-group">
            <input id="gateway_amount" class="form-control d-none" type="text" value="" placeholder="" onkeyup="check_amount(this)" tabindex="1">
            <div id="gateway_currency" class="input-group-append d-none">
              <span class="input-group-text">€</span>
            </div>
            <div class="invalid-feedback">
              <span id="gateway_amount_invalid">invalid ( format 0.00 )</span>
            </div>
          </div>
        </div>

        <div class="col-lg">
          <input id="gateway_concept" class="form-control" type="text" value="" placeholder="payment reference" onkeyup="check_reference(this)" tabindex="3">
          <div class="invalid-feedback">
            <span id="gateway_concept_invalid">reference too short, please enter a correct reference</span>
          </div>
        </div>
        <div class="col-lg">
          <button id="gateway_calculate" class="btn btn-info btn-block d-none" onclick="gateway_calc()">calculate payment</button>
          <button id="gateway_open" class="btn btn-primary btn-block d-none" onclick="gateway_start()">open Stargate</button>
        </div>
      </div>

      <div class="row line4 d-none">
        <div class="col-lg faircoin_uri">
          <a id="faircoin_uri" href=""><button class="btn btn-primary btn-block">FairCoin URI</button></a>
          <div id="qrcode"></div>
        </div>

        <div class="col-lg">
          <input id="fair_address" class="form-control" type="text" value="" readonly>
        </div>
        <div class="col-lg">
          <div class="input-group">
            <input id="fair_amount" class="form-control" type="text" value="" readonly>
            <div class="input-group-append">
              <span class="input-group-text">FAIR</span>
            </div>
          </div>
          <div id="tokeninfo"><p>Write the token to paper because you need it checking the state of transaction or getting support from Stargate team!</p>
            <h2 id="order_id"></h2>
          </div>
        </div>

      </div>

    </div>



    <script src="assets/js/qrcode.js"></script>

    <script>

      var LIMITS=Array(50,100,200,5000);
      var FAIR_ADDRESS="fVZoFndL2GK2pDnQUrciHerzjP5zwY4Evk";
      var FAIR_OFFICIAL_PRICE=1.2;
      var FAIR_FREE_MARKET=0;

      var J=[];  // stargate directory JSON
      var JJ=[]; // stargate selected order JSON

      var qrcode = new QRCode(document.getElementById("qrcode"), {
        width : 150,
        height : 150,
        colorDark : "#000000",
	       colorLight : "aliceblue",
	        correctLevel : QRCode.CorrectLevel.H
      });

      var F=[];

      $( document ).ready(function() {

        F = json_load( "assets/functions/get_free_market_price.php", "json" );

        if( F.length == 1 ){
          FAIR_FREE_MARKET=parseFloat( F[0].price_eur );
        } else {
          $(".line1").toggleClass("d-none",true);
          alert( "Can't get free market price !! Stargate gateway not available!!" );
        }

        J=json_load( "http://fairplayground.info/api/rawdata/STGT.geo.json","json" ).features[0];


        var order_id=getQueryVariable("order_id");

        if(order_id){

          $.post("assets/functions/get_items_json.php",
            {
                order_id: order_id,
            },
            function(data, status){
              if( status != "success") alert("Data: " + data + "\nStatus: " + status);

              JJ=JSON.parse(data);
              if( JJ.length == 1 ){

                J.forEach(
                  function(v,i){
                    if( v.properties.iban.toString().replace(/ /g,"").toUpperCase() == JJ[0].IBAN ){
                      entry_load(v);
                    }
                  }
                );
                $("#gateway_id").val(JJ[0].IBAN);

                $("#order_id").text(JJ[0].order_id);
                $("#tokeninfo p").text("You need the token below for checking the state of transaction or getting support from Stargate team!");

                $("#gateway_amount").val(JJ[0].amount).attr("readonly",true);
                $("#gateway_concept").val(JJ[0].concept).attr("readonly",true);;
                $("#fair_address").val(JJ[0].fair_address);
                $("#fair_amount").val(JJ[0].fair_amount);
                $(".container-fluid *").toggleClass("d-none",false);
                $(".line2").toggleClass("goBackground",true);
                $(".line3").toggleClass("goBackground",true);

                $("#gateway_calculate").toggleClass("d-none",true);
                $("#gateway_open").toggleClass("btn-primary",false).toggleClass("btn-success",true).text("Stargate is still open");

                makeCode("faircoin://" + JJ[0].fair_address + "?amount=" + JJ[0].fair_amount);

              }
           });

        } else {
          //search_stargate("");
        }

      });

      $(function () {
        $('[data-toggle="tooltip"]').tooltip()
      })

    </script>

    <script src="assets/js/TweenLite.min.js"></script>
		<script src="assets/js/EasePack.min.js"></script>
		<script src="assets/js/rAF.js"></script>
		<script src="assets/js/demo-1.js"></script>





    <script src="assets/js/stargate.js"></script>

  </body>
</html>
