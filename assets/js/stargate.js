


var GS="";

function search_stargate(obj){

  if( $("#gateway_id").attr("readonly") == "readonly" ) return;

  $(".line2").toggleClass("d-none",true);
  $(".line3").toggleClass("d-none",true);


  clearTimeout(GS);

  //if( a == "" ) return;

  if( JJ.length == 1 ){
    // prder found -> load details
    //$("#gateway_id").val(JJ[0].properties.IBAN);
  } else {
    // no order found -> search in directory
    GS=setTimeout(
      function(){
        search_stargate_directory(obj);
      },1000
    );
  }



}

function search_stargate_directory(obj){
  $("#result").html("");
  $(".line2").toggleClass("d-none",true);
  $("#gateway").toggleClass("d-none",true);
  $("#gateway_beneficiary").toggleClass("d-none",true);
  $("#gateway_address").toggleClass("d-none",true);
  $("#gateway_amount").toggleClass("d-none",true);
  $("#gateway_currency").toggleClass("d-none",true);
  $(obj).toggleClass("is-invalid",true);
  J.forEach(
    function(v,i){
      if( (v.properties.iban).replace(/ /g,"").match(eval("/^" + (obj.value).replace(/ /g,"") + "$/ig") ) != null ){
        entry_load(v);
        $(obj).toggleClass("is-invalid",false);
      }
    }
  );
}

var E={};  // selected entry

function entry_load(v){
  E = JSON.parse(JSON.stringify(v));
  $("#gateway").val("FAIR to SEPA");
  $("#gateway").toggleClass("d-none",false);
  $("#gateway_beneficiary").val(v.properties.iban_beneficiary);
  $("#gateway_beneficiary").toggleClass("d-none",false);
  $("#gateway_address").val(v.properties.iban);
  $("#gateway_address").toggleClass("d-none",false);
  $("#gateway_amount").toggleClass("d-none",false);
  $("#gateway_amount").prop("placeholder","0.00 - " + check_limit().toFixed(2) );
  $("#gateway_currency").toggleClass("d-none",false);

  $("#gateway_rating > i:first-child").toggleClass("rat_public",v.properties.popular);
  $("#t1").attr("data-original-title",((v.properties.popular) ? "high popular" : "not popular" ));

  $("#gateway_rating > i:nth-child(2)").toggleClass("rat_fair",v.properties.fair);
  $("#t2").attr("data-original-title",((v.properties.fair) ? "fair / cooperative working" : "not cooperative working" ));

  $("#gateway_rating > i:nth-child(3)").toggleClass("rat_faircoop",v.properties.faircoop);
  $("#t3").attr("data-original-title",((v.properties.faircoop) ? "known faircoop member" : "no faircoop member known" ));

  var score=0;
  if( v.properties.popular == true ) score++;
  if( v.properties.fair == true ) score++;
  if( v.properties.faircoop == true ) score++;

  // ##### get current free market price ############
  //FAIR_FREE_MARKET


  $("#payment_limit").text( LIMITS[score] );
  $("#exchange_price").text( (( v.properties.fair ) ? FAIR_OFFICIAL_PRICE : FAIR_FREE_MARKET ).toFixed(2) );

  $(".line2").toggleClass("d-none",false);
  $(".line3").toggleClass("d-none",false);


  $("#gateway_amount").focus();
  $("#gateway_id").prop("readonly",true);

  //var ask=confirm( "Please confirm limit and price or cancel\n\nlimit: " + $("#payment_limit").text() + "€\nprice: " + $("#exchange_price").text() + "€" );

  //$("#result").append("<li><a href='" + v.properties.url + "'>" + v.properties.name + "</a> (" + v.properties.iban + ")</li>" );
}

function check_limit(){
  var c=0;
  if( E.properties.popular == true ) c++;
  if( E.properties.fair == true ) c++;
  if( E.properties.faircoop == true ) c++;
  return LIMITS[c];
}

function check_amount(obj){

  //$(".line3").toggleClass("d-none",true);

  if( obj.value == "" ){
    $(obj).toggleClass("is-invalid",true);
    $(obj).toggleClass("is-valid",false);
    $("#gateway_amount_invalid").text("please enter an amount");
    return;
  }

  if( $.isNumeric(obj.value) == false ){
    $(obj).toggleClass("is-invalid",true);
    $(obj).toggleClass("is-valid",false);
    $("#gateway_amount_invalid").text("invalid format ( should be 0.00 )");
    return;
  }

  var l=check_limit();
  if( l < obj.value ){
    $(obj).toggleClass("is-invalid",true);
    $(obj).toggleClass("is-valid",false);
    $("#gateway_amount_invalid").text("limit exceed");
    return;
  }

  $(obj).toggleClass("is-valid", true);
  $(obj).toggleClass("is-invalid",false);
  $(".line3").toggleClass("d-none",false);

}

function check_reference(obj){
  if( obj.value.toString().length > 3 && $("#gateway_amount").val() > 0 ){
    $("#gateway_calculate").toggleClass("d-none",false);
    //$("#gateway_amount").prop("readonly",true);
    $("#gateway_concept").toggleClass("is-valid",true);
    $("#gateway_concept").toggleClass("is-invalid",false);
  } else {
    $("#gateway_calculate").toggleClass("d-none",true);
    //$("#gateway_amount").prop("readonly",false);
    $("#gateway_concept").toggleClass("is-valid",false);
    $("#gateway_concept").toggleClass("is-invalid",true);
  }
}


var token = function() {
    return Math.random().toString(36).substr(2); // remove `0.`
};

function gateway_calc(){

  $("#gateway_amount").prop("readonly",true);
  $("#gateway_concept").prop("readonly",true);

  $("#gateway_amount").val( parseFloat($("#gateway_amount").val()).toFixed(2) );

  $.post("assets/functions/create_item.php",
    {
        action: "calculate",
        iban: $("#gateway_id").val().replace(/ /g,"").toUpperCase(),
        amount: parseFloat( $("#gateway_amount").val() ).toFixed(2),
        concept: $("#gateway_concept").val().trim()
    },
    function(data, status){
      if( status != "success") alert("Data: " + data + "\nStatus: " + status);
      var A=JSON.parse( data );

      if( parseFloat(A.limit) != parseFloat( $("#payment_limit").text() ) ){
        alert(parseFloat(A.limit) + "|" + parseFloat( $("#payment_limit").text())+ " Limit client side doesn't match with server side! Please contact Admin");
        return;
      }
      if( parseFloat( A.exchange_price ) != parseFloat( $("#exchange_price").text()) ){
        alert( parseFloat( A.exchange_price ) + "|" + parseFloat( $("#exchange_price").text()) + " Exchange price client side doesn't match with server side! Please contact Admin");
        return;
      }

      $("#fair_amount").val( parseFloat(A.fair_amount).toFixed(8));
      $(".faircoin_uri").toggleClass("d-none",true);
      $("#tokeninfo").toggleClass("d-none",true);
      $(".line4").toggleClass("d-none",false);
      $("#gateway_calculate").toggleClass("d-none",true);
      $("#gateway_open").toggleClass("d-none",false);

   });
}

var opencnt=0;  // prevent double click after calculate

function gateway_start(){

  if( $("#gateway_open").hasClass("btn-success") == true ){
    alert("Stargate is already open! Now you need to pay FAIR if not yet done!");
    return;
  }

  opencnt++;

  if( opencnt < 2 ) return;

  $.post("assets/functions/create_item.php",
    {
        action: "open",
        iban: $("#gateway_id").val().replace(/ /g,"").toUpperCase(),
        amount: parseFloat( $("#gateway_amount").val() ).toFixed(2),
        concept: $("#gateway_concept").val().trim()
    },
    function(data, status){
      if( status != "success") alert("Data: " + data + "\nStatus: " + status);

      var A=JSON.parse( data );

      if( parseFloat(A.limit) != parseFloat( $("#payment_limit").text() ) ){
        alert(parseFloat(A.limit) + "|" + parseFloat( $("#payment_limit").text()) + " Limit client side doesn't match with server side! Please contact Admin");
        return;
      }
      if( parseFloat( A.exchange_price ) != parseFloat( $("#exchange_price").text()) ){
        alert( parseFloat( A.exchange_price ) + "|" + parseFloat( $("#exchange_price").text()) + " Exchange price client side doesn't match with server side! Please contact Admin");
        return;
      }

      $("#fair_amount").val( parseFloat(A.fair_amount).toFixed(8));
      $("#fair_address").val(A.fair_address);
      $(".faircoin_uri").toggleClass("d-none",false);


      $("#gateway_open").toggleClass("btn-primary",false);
      $("#gateway_open").toggleClass("btn-success",true);
      $("#gateway_open").text("Stargate is now open");
      $(".line4").toggleClass("d-none",false);

      $(".line1").toggleClass("goBackground",true);
      $(".line2").toggleClass("goBackground",true);
      $(".line3").toggleClass("goBackground",true);
      makeCode("faircoin://" + A.fair_address + "?amount=" + parseFloat(A.fair_amount).toFixed(8));

      $("#faircoin_uri").prop("href","faircoin://" + A.fair_address + "?amount=" + parseFloat(A.fair_amount).toFixed(8));
      $("#order_id").text( A.order_id );
      $("#tokeninfo").toggleClass("d-none",false);

      ChangeUrl("FairCoin Stargate","http://fairplayground.info/FairCoin-Stargate/index.php?order_id=" + A.order_id);

   });

   setTimeout( function(){ checkPayment(); }, 60000 );
}

function checkPayment(){

  $.get("assets/functions/notification.php",
    {
        order_id: $("#order_id").text()
    },
    function(data, status){
      if( status != "success") alert("Data: " + data + "\nStatus: " + status);
      console.log(data);
      setTimeout( function(){ checkPayment(); }, 60000 );
   });

}

function ChangeUrl(page, url) {
    if (typeof (history.pushState) != "undefined") {
        var obj = { Page: page, Url: url };
        history.pushState(obj, obj.Page, obj.Url);
    } else {
        alert("Browser does not support HTML5.");
    }
}

function makeCode (elText) {

	if (!elText) {
		alert("Input a text");
		elText.focus();
		return;
	}

	qrcode.makeCode(elText);
}


function getQueryVariable(variable)
{
       var query = window.location.search.substring(1);
       var vars = query.split("&");
       for (var i=0;i<vars.length;i++) {
               var pair = vars[i].split("=");
               if(pair[0] == variable){return pair[1];}
       }
       if( variable == "coin" ){ return "btc"; } else { return(false); }

}


function json_load( url, type ){
  var json = null;
  $.ajax({
      'type':"GET",
      'async': false,
      'global': false,
      'cache': false,
      'url': url,
      'dataType': type,
      'success': function (data) {
          json = data;
      }
  });
  return json;
}
