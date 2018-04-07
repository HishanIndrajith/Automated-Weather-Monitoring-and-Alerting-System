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
$date_selected=$_GET['date_selected'];
$location=$_GET['location'];
$mysql_qry = "SELECT date,current_rainfall FROM data_set1 WHERE date >= '".$date_selected." 00:00:00' AND date <= '".$date_selected." 23:59:59'  AND location = ". $location." ORDER BY date ASC ";
$result = mysqli_query($conn,$mysql_qry);
$myJSON="";
if(mysqli_num_rows($result) > 0){
    $ret = array();
    $no=1;
    $ret[0]=["time","rainfall (mm)"];//headers
    while($row = $result->fetch_assoc()){
        $arr=array();
        $pieces = explode(" ", $row["date"]);
        $arr[0] = $pieces[1];
        $arr[1] = intval($row["current_rainfall"]);
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
