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
    <link href="../css/parallax.css" rel="stylesheet">
    <link href="../css/bootswatch_solar.css" rel="stylesheet">
    <link href="../css/demo.css" rel="stylesheet">
    <link href="../css/component.css" rel="stylesheet">
    <link href='https://fonts.googleapis.com/css?family=Raleway:200,400,800' rel='stylesheet' type='text/css'>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <link href="../css/bootswatch_solar.css" rel="stylesheet">
    <style>
    .list_items_headline {
      background:rgba(0,0,0,0.5);
    }
    .list_items_table {
      border-bottom:1px solid rgba(0,0,0,0.5);
    }

    button {
      margin-bottom:0.3em;
    }
    </style>
  </head>
  <body>
  <script src="../js/jquery.min.js"></script>
  <script src="../js/bootstrap.min.js"></script>
  <script src="../js/bootstrap.bundle.min.js"></script>

  <link href='https://fonts.googleapis.com/css?family=Lato:300,400,700' rel='stylesheet' type='text/css'>
  <div id='stars'></div>
  <div id='stars2'></div>
  <div id='stars3'></div>

  <div class="container-fluid">
    <div class="demo-1">
      <div class="content">
        <div id="large-header" class="large-header" style="height: 100%;">
          <canvas id="demo-canvas" width="1323" height="402"></canvas>
        </div>
      </div>
    </div>

    <div class="row">
      <div id="items" class="col">

      </div>
    </div>
  </div>

  <script>



  var J={};
  var JJ={};

  $( document ).ready(function() {
    J=json_load( "http://fairplayground.info/api/rawdata/STGT.geo.json","json" ).features[0];
    JJ=json_load( "http://fairplayground.info/FairCoin-Stargate/assets/functions/get_items_json.php","json" );
    list_items();
  });


  </script>

<script src="../js/TweenLite.min.js"></script>
<script src="../js/EasePack.min.js"></script>
<script src="../js/rAF.js"></script>
<script src="../js/demo-1.js"></script>
<script src="../js/list_items.js"></script>
</body>
</html>
