<?php
 session_start();
 if(isset($_SESSION['seen'])){
   unset($_SESSION['seen']);
 }
 if(isset($_SESSION['pagesToCrawl'])){
   unset($_SESSION['pagesToCrawl']);
 }
?>
<!DOCTYPE html>
<html>
	<head>
	  <meta name="viewport" content="width=device-width, initial-scale=1">
	  <script type="text/javascript" src="myjavascript.js"></script>
	  <link rel="stylesheet" href="styles.css">
	  <link rel="shortcut icon" href="img/logo.jpg">
	  <title>Indexer</title>
	</head>



<!-- Jquery to pull the navbar into every file -->
<script src="//code.jquery.com/jquery.min.js"></script>
<script>
  $.get("adminnavbar.html", function(data){
     $("#nav-placeholder").replaceWith(data);
  });
</script>
<!--End topNavBar-->
<body>

	<!--Start topNav Bar-->
	<div id="nav-placeholder">
	</div>
	
	<div class="navigator">
		<h3><img src="img/list.png" width="90px;"> Indexer</h3>
			
		<form action='webcrawler.php' method='POST'>
		
			<div class="search_div">
				<div class="search_head">
					<input name='url' id="search_text" type="text" placeholder="Type url to index..." name="search">
					<button type='submit' id="search_button">Index</button>
				</div>
			</div>
		</form>
	</div>
	
</body>
</html>
