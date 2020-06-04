
<!DOCTYPE html>
<html>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<script type="text/javascript" src="myjavascript.js"></script>
		<link rel="stylesheet" href="styles.css">
		<link rel="shortcut icon" href="sara-icon.png">
		<title>Search Results</title>
	</head>
<body>

	<!--Start topNav Bar-->
	<div id="nav-placeholder">
	</div>
	<!-- Jquery to pull the navbar into every file -->
	<script src="https://code.jquery.com/jquery-3.4.0.min.js"
				  integrity="sha256-BJeo0qm959uMBGb65z40ejJYGSgR7REI4+CW1fNKwOg="
				  crossorigin="anonymous"></script>
	<script>
	  $.get("adminnavbar.html", function(data){
		 $("#nav-placeholder").replaceWith(data);
	  });
	</script>
	<!--End topNavBar-->
	
	
	<div class = "navigator">
		
		<form action="searchSARA.php" class="searchinfo" method="POST">
			
			<div class="search_div">
				<div class="search_options">
					
					<h5>Options:</h5>
					<input type="checkbox" id= "case" name="case" value = "CS"  <?php if(isset($_POST["case"])) echo 'checked';?>>
					<label>Case Sensitive</label>
					
					<h5>Search position:</h5>
					<input type="radio" id= "exactt" name="exact" value="exact" <?php if($_POST['exact'] == "exact") echo 'checked';?> >
					<label>Exact Match</label>
					<input type="radio" id= "exact" name="exact" value="partial" <?php if($_POST['exact'] == "partial") echo 'checked';?>>
					<label>Partial Match</label>
					
					<h5>Search Type:</h5>
					<input type="radio" name="searchtype" value="AND" <?php if($_POST['searchtype'] == "AND") echo 'checked';?> >
					<label>AND</label>
					<input type="radio" name="searchtype" value="OR" <?php if($_POST['searchtype'] == "OR") echo 'checked';?>>
					<label>OR</label>
					<input type="checkbox" name="limit" value="true"  <?php if(isset($_POST["limit"])) echo 'checked';?>>
					<label>GET TOP 10 Results Only</label>
					
				</div>
			</div>
			
			
			<div class="search_div">
				<div class="search_head">
					<img src="img/logo.jpg" id="G_image" width="170px">
					<input id="search_text" type="text" placeholder="Search..." name="q" value = "<?php echo $_POST['q'];?>">
					<button id="search_button"><i class="fa fa-search"></i></button>
				</div>
			</div>
		
		</form>
		
		<div id="ret" class="search_div1">
			
			<label><input type="checkbox" id="selectAll" onClick="toggleChecks(this)">Select All</label>
				
			<select class="hideDownload" id="dtype">
				<option value="json">JSON</option>
				<option value="csv">CSV</option>
				<option value="xml">XML</option>
			</select>
			<input type="button" id="savebutton" value="Download file" onclick="saveResults()" class="hideDownload">
			
		</div>
		
		<div class = "search-results">
			
			<div id="page-nav">
				<span class='prev' id='prev'>PREVIOUS</span>
				<span class='next' id='next'>NEXT</span>
			</div>
		
			<div id="displayTotal"></div>
			<div id="resultsDisplay"></div>
		</div>
			
		

			<?php
			$numterms = preg_match_all("/\w+/", $_POST["q"], $terms);
			if($numterms>0){
				$start =microtime(true);
				$query = "";
				$prefix = "";
				$suffix = "";
				$exact = $_POST["exact"];
				$case1 = "";
				$case2 = " ";
				if($exact=="partial"){
						$exact="LIKE ";
						$prefix = "%";
						$suffix = "%";
					
				}else{
					$exact= "= ";
				}
				if(!isset($_POST["case"])){
					$case1 = "LOWER(";
					$case2 = ") ";
				}
				//OR CASE
				if($_POST["searchtype"]=="OR"){//Search for at least 1 term in the query
					//AND CASE
					$query .= "SELECT url, title, description, lastIndexed FROM ";
					//Subquery for word i
					for($i = 0;$i<$numterms;$i++){
						$query .= "(SELECT pageId, SUM(freq) total FROM pageword pw, word w WHERE w.wordId = pw.wordId AND " . $case1 . "w.wordName" . $case2 . $exact . $case1 . "'" . $prefix . $terms[0][$i] . $suffix . "'" . $case2 .
								"GROUP BY pageId) pw" . $i . ", ";
					}
					$query .= "page p WHERE ";
					for($i = 0;$i<$numterms;$i++){
						$query .= "p.pageId = pw". $i . ".pageId ";
						if($i<$numterms-1){
							$query .= "OR ";
						}
					}
					$query .= "GROUP BY url";
					$query .= " ORDER BY ";
					for($i = 0;$i<$numterms;$i++){
						$query .= "pw" . $i . ".total ";
						if($i<$numterms-1){
							$query .= "+ ";
						}else{
							$query .= "DESC";
						}
					}
					//$query .= "GROUP BY url ORDER BY SUM(freq) desc";
				}else{
				//AND CASE
					$query .= "SELECT url, title, description, lastIndexed FROM ";
					//Subquery for word i
					for($i = 0;$i<$numterms;$i++){
						$query .= "(SELECT pageId, SUM(freq) total FROM pageword pw, word w WHERE w.wordId = pw.wordId AND " . $case1 . "w.wordName" . $case2 . $exact . $case1 . "'" . $prefix . $terms[0][$i] . $suffix . "'" . $case2 .
								"GROUP BY pageId) pw" . $i . ", ";
					}
					$query .= "page p WHERE ";
					for($i = 0;$i<$numterms;$i++){
						$query .= "p.pageId = pw". $i . ".pageId ";
						if($i<$numterms-1){
							$query .= "AND ";
						}
					}

					$query .= "ORDER BY ";
					for($i = 0;$i<$numterms;$i++){
						$query .= "pw" . $i . ".total ";
						if($i<$numterms-1){
							$query .= "+ ";
						}else{
							$query .= "DESC";
						}
					}
				}
				
				//Connect to database
				$servername = "127.0.0.1";
				$username = "root";
				$password = "";
				$database = "sara";
				$mysqli = new mysqli($servername, $username, $password, $database);

				//Submit $query
				$results = $mysqli->query($query);
				/*
				if(!$result){
					$error = "Error($mysqli->errno) $mysqli->error<br> $query";
					$jsonResult = json_encode($error);
					$mysqli->close();
				}else{
				*/
				$toDisplay = array();
				$count= 0;
				while($row = $results->fetch_assoc()){
						if(isset($_POST['limit'])){
						if($count<10){
						$array = array('url'=>$row['url'], 'title'=>$row['title'], 'description'=>$row['description']);
						array_push($toDisplay, $array);
						}
						$count++;
						}else{
							$array = array('url'=>$row['url'], 'title'=>$row['title'], 'description'=>$row['description']);
							array_push($toDisplay, $array);
							$count++;
						}
				}
				$result = array("Result" => $toDisplay);
				$jsonResult = json_encode($result);
				//For entering into the search db
				$totaltime = microtime(true) - $start;
				$date = date('Y-m-d H:i:s');
				$insertquery = "INSERT INTO search(terms, count, searchDate, timeToSearch) VALUES ('";
				$insertquery .= $_POST["q"] . "', $count, '$date', $totaltime)";
				if(!$mysqli->query($insertquery)){
						  die("Error($mysqli->errno) $mysqli->error<br> $insertquery");
				}
				$mysqli->close();

			}else{
					$result =array("Result" => array());
					$jsonResult = json_encode($result);
			}
			?>
			<script>
				$(document).ready(function(){
					var result = <?php echo $jsonResult ?>;
					if(result.Result==null || result.Result.length==0){
						document.getElementById("resultsDisplay").innerHTML = "No results found.";
								$('#page-nav').hide();
					}else{
						dbjsonReader(result);
						pagination();
					}
				});
			</script>
				
		</div>
		
		<div class="page-data"></div> 
		
	</div>
	
	
	
	


</body>

</html>
