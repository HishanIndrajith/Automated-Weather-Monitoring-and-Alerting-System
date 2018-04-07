<?php
require "conn.php";
$temp = $_GET["temp"];
$hum = $_GET["hum"];
$wind_speed = $_GET["wind_speed"];
$is_raining = $_GET["is_raining"];
$current_rainfall = $_GET["current_rainfall"];
$location = $_GET["location"];
date_default_timezone_set("Asia/Colombo");
$timeslot= date("a");
$date = date("Y-m-d");
if(strcmp($timeslot,"pm")==0){
    $date = $date . " " . (date("h")+12) . date(":i:s");
}else{
    $date = $date . " " . date("h:i:s"); 
}

$mysql_qry = "insert into data_set1 (date,temp,hum,wind_speed,is_raining,current_rainfall,location) values ('$date','$temp','$hum','$wind_speed','$is_raining ','$current_rainfall ','$location');";
if(mysqli_query($conn,$mysql_qry)===TRUE){
	$myfile1 = fopen("locations/". $location . ".txt", "w");
	fwrite($myfile1, $date);
	fclose($myfile1);
	$myfile2 = fopen("summary.txt", "w");
	fwrite($myfile2, $date);
	fclose($myfile2);
	echo "1";
}else{
	echo "0";
}

mysqli_close($conn);

?>