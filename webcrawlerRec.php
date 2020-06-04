<?php

require('simple_html_dom.php');
session_start();
if(isset($_POST['url'])){
  crawl_page($_POST['url']);
}else{
  echo "No URL entered.";
}
function crawl_page($url){
    //Check if the page has been seen
    //--Access database to see if site has been indexed
  $servername = "127.0.0.1";
$username = "root";
$password = "";
    $database = "sara";
    $mysqli = new mysqli($servername, $username, $password, $database);
    // Check connection
    if ($mysqli->connect_errno) {
      die("1 Connection failed: " . mysqli_connect_error());
    }

    $seen = array();
    $pagequery = "SELECT url, lastIndexed from Page";
    $result = $mysqli->query($pagequery);
    if($result){
        while($row = $result->fetch_assoc()){
            $seen[$row['url']] = $row['lastIndexed'];
        }
    }
    $_SESSION['seen']=$seen;
    if(isset($seen[$url]) && (strtotime(date('Y-m-d H:i:s'))-strtotime($seen[$url])<604800)){
      getNextUrl($url, $mysqli);
    }else{
      $start =microtime(true);
      $dom = new DOMDocument('1.0');
      @$dom->loadHTMLFile($url);
      getPagesToCrawl($url, $mysqli, $dom);

      $title = getTitle($url, $dom);
      if($title==false){
        getNextUrl($url, $mysqli);
      }else{
        $title =$mysqli->real_escape_string($title);
        if(strlen($title)>100){
          $title = substr($title, 0, 100);
        }
        $description = getDescription($url);
        $description = $mysqli->real_escape_string($description);
        if(strlen($description)>500){
          $description = substr($description, 0, 500);
        }
        $date = date('Y-m-d H:i:s');

        if(!isset($seen[$url])){
            //--Insert the page to the database
            $newpagequery = "INSERT INTO Page(url, title, description, lastIndexed) VALUES ('$url', '$title','$description', '$date')";
            if(!$mysqli->query($newpagequery)){
            die("5 Error($mysqli->errno) $mysqli->error<br> url: $url,title: $title<br>date: $date");
            }
            $pageid = $mysqli->insert_id;
            addToPageWord($url, $pageid, $mysqli);
            $timetaken = microtime(true)-$start;
            $updatequery = "UPDATE Page SET timeToIndex = $timetaken WHERE page_id = $pageid";
        }else{
            //--Update the page in the database
            $pagequery = "SELECT page_id FROM Page WHERE url = '$url' LIMIT 1";
            $result = $mysqli->query($pagequery);
            $value = $result->fetch_object();
            $pageid = $value->page_id;
            $deletequery = "DELETE FROM PageWord WHERE page_id ='$pageid'";
            $mysqli->query($deletequery);
            addToPageWord($url, $pageid, $mysqli);
            $timetaken = microtime(true)-$start;
            $updatequery = "UPDATE Page SET title = '$title', description = '$description', timeToIndex = $timetaken WHERE page_id = $pageid";
        }
        if(!$mysqli->query($updatequery)){
            die("5 Error($mysqli->errno) $mysqli->error<br> $updatequery");
        }
        getNextUrl($url, $mysqli);
      }

    }
}
function addToPageWord($url, $pageid, $mysqli){
  //Get the text of the site
  $str = file_get_html($url)->plaintext;
  //Get the words and count for each word for the page
  $page_word = array_count_values(str_word_count(strip_tags($str), 1));
  //--Access database and insert to page_word table and word table if word is not already there
  $insertquery = "INSERT INTO PageWord(page_id, word_id, word_count) VALUES ";
  foreach($page_word as $inputword => $count){
      $word = $mysqli->real_escape_string($inputword);
      $words = $mysqli->query("SELECT word_id from Word WHERE word = '$word' limit 1");
      if($words && $words->num_rows>0){
        $value=$words->fetch_object();
        $wordid = $value->word_id;
      }else{
        $checkquery = "INSERT INTO Word(word) VALUES ('$word')";
        $check = $mysqli->query($checkquery);
        if(!$check){
          die("6 Error($mysqli->errno) $mysqli->error<br> $checkquery");
        }
        $wordid = $mysqli->insert_id;
      }
       $insertquery.="('$pageid', '$wordid', '$count'), ";
  }
  $insertquery = substr($insertquery, 0, -2);
  $checkinsert = $mysqli->query($insertquery);
  if(!$checkinsert){
    die("7 Error($mysqli->errno) $mysqli->error<br> $insertquery");
  }
}
//Get the description
function getDescription($url){
  $html = file_get_html($url);
  $description = "No description found.";
  $descnode = $html->find('meta[name="description"]', 0);
  if($descnode == null){
      $descnode = $html->find('meta[property="og:description"]', 0);
      if($descnode == null){
            $descnode = $html->find("p", 0);
            if($descnode == null){
                $description = $html->plaintext;
            }
      }else{
          $description = $descnode->content;
      }
  }else{
      $description = $descnode->content;
  }
  return $description;
}
//Get the title
function getTitle($url, $dom){
  $titleNodes = $dom->getElementsByTagName('title');
  $title="";
  if($titleNodes->length>0){
    $title = $titleNodes->item(0)->textContent;
    return $title;
  }
  if($title==""){
    if(!$str = @file_get_contents($url))
      return false;
    if(strlen($str)>0){
      $str = trim(preg_replace('/\s+/', ' ', $str)); // supports line breaks inside <title>
      preg_match("/\<title\>(.*)\<\/title\>/i",$str,$title); // ignore case
      return $title[1];
    }
  }
}
//Get pages to crawl
function getPagesToCrawl($url, $mysqli, $dom = null){
  //Get all the hrefs on the page to crawl through
  if(!$dom){
    $dom = new DOMDocument('1.0');
    @$dom->loadHTMLFile($url);
  }
  $count = 0;
  $toCrawl = array();
  $anchors = $dom->getElementsByTagName('a');
  foreach ($anchors as $element) {
      $href = $element->getAttribute('href');

      if(0===strpos($href, '#')){
        continue;
      }
      if (0 !== strpos($href, 'http')) {
          $path = '/' . ltrim($href, '/');
          if (extension_loaded('http')) {
              $href = http_build_url($url, array('path' => $path));
          } else {
              $parts = parse_url($url);
              $href = $parts['scheme'] . '://';
              if (isset($parts['user']) && isset($parts['pass'])) {
                  $href .= $parts['user'] . ':' . $parts['pass'] . '@';
              }
              $href .= $parts['host'];
              if (isset($parts['port'])) {
               $href .= ':' . $parts['port'];
              }
              if( (1 !== strpos($path, dirname($parts['path'], 1))) && (0 !== strpos($path, dirname($parts['path'], 1)))  ){
                  $href .= dirname($parts['path'], 1).$path;
              }else{
                $href .= $path;
              }
          }
      }
      $hparts = parse_url($href);
      if(isset($hparts['fragment'])){
          $href = strstr($href, '#', true);
      }
      if(isset($hparts['query'])){
        $href = strstr($href, '?', true);
      }
      //--Insert href to pages to crawl table
      //--Check against pages indexed/pages to crawl
      $href = rtrim($href, '/');
      $href = $mysqli->real_escape_string($href);
      if(strlen($href)>200){
        continue;
      }

      $indexCheck = false;
      $seen = $_SESSION['seen'];
      if(in_array($href, $seen)){
        $indexCheck = true;
      }
      $crawlCheck = false;
      if(in_array($href, $toCrawl)){
        $crawlCheck = true;
      }
      if($indexCheck || $crawlCheck){
        continue;
      }else{
        array_push($toCrawl, $href);
        $count++;
      }
  }
  if($count!=0){
      $_SESSION['pagesToCrawl'] = $toCrawl;
  }
}
function getNextUrl($url, $mysqli){
  if(!isset($_SESSION['pagesToCrawl'])){
      getPagesToCrawl($url, $mysqli);
  }
  $arr=$_SESSION['pagesToCrawl'];
  if(isset($arr[$url])){
    unset($arr[$url]);
  }
  $_SESSION['seen'][$url] = date('Y-m-d H:i:s');
  $mysqli->close();
  if($arr){
    echo "<form action='webcrawler.php' method ='POST'>";
    foreach($arr as $nexturl){
        echo "<label><input type='radio' id='url' name='url' value='$nexturl'>$nexturl</label><br>";
    }
    echo "<button type='submit' id = 'submit'>ENTER</button></form>";
  }else{
      echo "No more URLs";
  }

}
?>
