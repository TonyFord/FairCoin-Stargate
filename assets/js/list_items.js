function list_items(){
  $("#items").html("");
  var t="";
  t+="<div class='row list_items_headline'>";
  t+="<div class='col-lg-2 col-md-3'><b>TIMESTAMP</b></div>";
  t+="<div class='col-lg-4 col-md-4'><b>IBAN</b></div>";
  t+="<div class='col-lg-2 col-md-2 text-right'><b>AMOUNT</b></div>";
  t+="<div class='col-lg-4 col-md-3'><b>CONCEPT</b></div>";
  t+="<div class='col-lg-2 col-md-3'><b>STATE (ORDER_ID)</b></div>";
  t+="<div class='col-lg-4 col-md-4'><b>FAIRCOIN_ADDRESS</b></div>";
  t+="<div class='col-lg-3 col-md-3 text-right'><b>FAIRCOIN_AMOUNT</b></div>";

  t+="</div>";
  $("#items").append(t);
  JJ.forEach(
    function(v,i){
      var t="";
      t+="<div class='row list_items_table'>";
      t+="<div class='col-lg-2 col-md-3'>" + v.tstamp + "</div>";
    //  t+="<div class='col-lg-2'>" + v.order_id + "</div>";
      var iban_beneficiary=""; var bic="";
      J.forEach(
        function( w,j ){
          if( w.properties.iban.replace(/ /g,"").toUpperCase() == v.IBAN ){
            iban_beneficiary = " - " + w.properties.iban_beneficiary;
            bic = (w.properties.bic).replace(/ /g,"");
          } else {
            iban_beneficiary = " - <font color='red'> (not found!! pls check)</font><br>";
          }
        }
      );
      t+="<div class='col-lg-4 col-md-4'>" + v.IBAN + " " + bic + " " + iban_beneficiary + "</div>";
      t+="<div class='col-lg-2 col-md-2 text-right'>" + v.amount.toFixed(2) + " &euro;</div>";
      t+="<div class='col-lg-4 col-md-3'>" + v.concept + "</div>";

      var state_info="";
      var state_class="";
      switch(v.state){
        case 5:
          state_info="05_wait_FAIR";
          state_class="btn-danger";
          break;
        case 6:
          state_info="06_send_FIAT";
          state_class="btn-primary";
          break;
        case 7:
          state_info="done";
          state_class="btn-success";
          break;
      }

      t+="<div class='col-lg-2 col-md-3'><button class='btn " + state_class + " btn-sm' onclick='list_items_update(\"" + v.order_id + "\"," + v.state + ", this )'>" + state_info + "</button> &nbsp;" + v.order_id + "</div>";
      t+="<div class='col-lg-4 col-md-4'><a href='https://chain.fair.to/address?address=" + v.fair_address + "'>" + v.fair_address + "</a></div>";
      t+="<div class='col-lg-3 col-md-3 text-right'>" + v.fair_amount.toFixed(8) + " FAIR</div>";
      t+="</div>"
      $("#items").append(t);
    }
  );
}


function list_items_update(order_id, state, obj){
  var c=prompt("Enter code");

  $.post("update_list_items.php",
    {
        order_id: order_id,
        c:c ,
        state: state
    },
    function(data, status){
        alert("Data: " + data + "\nStatus: " + status);
        if( data == "OK" ){

          if( $(obj).hasClass("btn-danger") ){
            $(obj).text("06_send_FIAT");
            $(obj).toggleClass("btn-danger");
            $(obj).toggleClass("btn-primary");
          } else if( $(obj).hasClass("btn-info") ){
            $(obj).text("done");
            $(obj).toggleClass("btn-info");
            $(obj).toggleClass("btn-success");
          }

        }
   });

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
