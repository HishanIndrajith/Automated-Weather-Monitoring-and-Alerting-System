<?php
require "conn.php";
$temp = $_GET["temp"];
$hum = $_GET["hum"];
$wind_speed = $_GET["wind_speed"];
$wind_dir = $_GET["wind_dir"];
$location = $_GET["location"];
date_default_timezone_set("Asia/Colombo");
$timeslot= date("a");
$date = date("Y-m-d");
if(strcmp($timeslot,"pm")==0){
    $date = $date . " " . (date("h")+12) . date(":i:s");
}else{
    $date = $date . " " . date("h:i:s"); 
}

$mysql_qry = "insert into data_set1 (date,temp,hum,wind_speed,wind_dir,location) values ('$date','$temp','$hum','$wind_speed','$wind_dir','$location');";
if(mysqli_query($conn,$mysql_qry)===TRUE){
	$qry = "UPDATE location SET th_last_update = '" . $date . "' WHERE location.id = " . $location . ";";
	if(mysqli_query($conn,$qry)===TRUE){
		echo "1";
	}
}else{
	echo "0";
}



?>