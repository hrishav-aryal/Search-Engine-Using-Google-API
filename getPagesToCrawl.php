<?php
    session_start();
    if(isset($_SESSION['pagesToCrawl'])){
      echo $_SESSION['pagesToCrawl'];
    }else{
      $arr = array("No Pages found");
      echo json_encode($arr, true);
    }
?>
