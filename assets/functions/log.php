<?php

function add_log($file, $info){
  
  $fp=fopen($file,"r");
  $log=fread($fp,10000);
  fclose($fp);

  $log=date("Y-m-d H:i:s",time()).",".$info."
  ".$log;

  $fp=fopen( $file,"w+");
  fwrite($fp,$log);
  fclose($fp);

}



?>
