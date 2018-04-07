<?php
/*Long Polling PHP server script for live temperature and humidity updates.
This server side php returns a json array abject containing the new temperature and humidity updates at the database. 
The script uses long polling technology.
The last time of update is saved in a file in server. clients must send a HTTP GET request containing the last time client was updated as soon as an update happen using AJAX.
This script waits for a new server update and notify the client through a JSON object containing new data and the updated time.
So front end will be updated with live data.

Author:Hishan Indrajith ** 
University of Peradeniya **
hishan.indrajith.95hia@gmail.com **
17-03-2018

Technologies used - PHP5, JSON, MYSQL, Long Polling Concept
*/
require "conn.php";
$year_selected=$_GET['year'];
$location=$_GET['location'];
$mysql_qry = "SELECT MAX(hum) AS max,MIN(hum) AS min, CAST(date AS DATE) AS date
FROM data_set1 WHERE date >= '".$year_selected."-01-01 00:00:00' AND date <= '".$year_selected."-12-31 23:59:59'  AND location = ". $location."
GROUP BY CAST(date AS DATE) ORDER BY date ASC ;";
$result = mysqli_query($conn,$mysql_qry);
$myJSON="";
if(mysqli_num_rows($result) > 0){
    $ret = array();
    $no=1;
    $ret[0]=["time","max-humidity","min-humidity"];//headers
    while($row = $result->fetch_assoc()){
        $arr=array();
        $arr[0] = $row["date"];
        $arr[1] = intval($row["max"]);
        $arr[2] = intval($row["min"]);
        $ret[$no] = $arr;
        $no++;
    }
    $myJSON = json_encode($ret);
    echo $myJSON;
}else{
    echo "0";
}
mysqli_close($conn);

?>
