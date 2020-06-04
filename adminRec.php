<?php
 session_start();
 if(isset($_SESSION['seen'])){
   unset($_SESSION['seen']);
 }
 $myarr = array();
 $_SESSION['pagesToCrawl'] = json_encode($myarr, JSON_FORCE_OBJECT);
 $_SESSION['count'] = 0;

?>
<!DOCTYPE html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script type="text/javascript" src="../myjavascript.js"></script>
  <link rel="stylesheet" href="../styles.css">
  <link rel="shortcut icon" href="../sara-icon.png">
  <title>Recursive</title>
</head>
<!--Start page heading-->
<div id="pageName">
	<p>Recursive</p>
</div>
<!--End page heading-->

<!--Start topNav Bar-->
<div id="nav-placeholder">
</div>
<!-- Jquery to pull the navbar into every file -->
<script src="//code.jquery.com/jquery.min.js"></script>
<script>
  $.get("adminnavbar.html", function(data){
     $("#nav-placeholder").replaceWith(data);
  });
</script>
<!--End topNavBar-->
<body>
<form action='webcrawlerRec.php' method='POST'>
    <label><input type='text' id='url' name='url'>URL TO INDEX</label><br>
    <button type='submit' id = 'submit'>ENTER</button>
  </form>
</body></html>
