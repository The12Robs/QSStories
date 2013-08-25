<?php  

require("phpsqlajax_dbinfo.php"); 

// Start XML file, create parent node

$dom = new DOMDocument("1.0");
$node = $dom->createElement("records");
$parnode = $dom->appendChild($node); 

// Opens a connection to a MySQL server

$connection=mysql_connect (localhost, $username, $password);
if (!$connection) {  die('Not connected : ' . mysql_error());} 

// Set the active MySQL database

$db_selected = mysql_select_db($database, $connection);
if (!$db_selected) {
  die ('Can\'t use db : ' . mysql_error());
} 

// Select all the rows in the markers table

$query = "(select 'op' as source, '' as path, lattitude, longitude, ADDTIME(timestamp, '-07:00:00') as timestamp from openpath where ADDTIME(timestamp, '-07:00:00') between '2013-06-16' and '2013-06-17')";
$query .= " UNION (select 'gl' as source, '' as path, cast(latitudeE7/10000000 AS DECIMAL(10,7)) as lattitude, cast(longitudeE7/10000000 AS DECIMAL(10,7)) as longitude, timestamp from  googlelocation where timestamp between '2013-06-16' and '2013-06-17')";
$query .= " UNION (select 'im', '' as path, gpslatitude as lattitude, gpslongitude as longitude, created as timestamp from image where gpslatitude IS NOT NULL and created between '2013-06-16' and '2013-06-17')";
$query .= " UNION (select '' as source,  path, '' as lattitude, '' as longitude, created as timestamp from image where created between '2013-06-16' and '2013-06-17')";
$query .= " order by timestamp";

$result = mysql_query($query);
if (!$result) {  
  die('Invalid query: ' . mysql_error());
} 

header("Content-type: text/xml"); 

// Iterate through the rows, adding XML nodes for each

while ($row = @mysql_fetch_assoc($result)){  
  // ADD TO XML DOCUMENT NODE  
  $node = $dom->createElement("record");  
  $newnode = $parnode->appendChild($node);   
  $newnode->setAttribute("path", $row['path']);  
  $newnode->setAttribute("lat", $row['lattitude']);  
  $newnode->setAttribute("lng", $row['longitude']);  
  $newnode->setAttribute("timestamp", $row['timestamp']);
  $newnode->setAttribute("source", $row['source']);
} 

echo $dom->saveXML();

?>