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
set_time_limit(0); // maximum execution time is set unlimited
require "conn.php";
echo poll($_GET['th_last_updated_time'],$conn);
function poll($th_last_updated_time,$conn){
    // 
    //require "conn.php";
    $location = $_GET['location'];
    $server_updated_time = file_get_contents('locations/'. $location . '.txt');
    //
    if($server_updated_time == $th_last_updated_time){
        // 
        sleep(1); 
        return poll($th_last_updated_time,$conn);
    }else{
        date_default_timezone_set("Asia/Colombo");
        $date = date("Y-m-d");
        $mysql_qry = "SELECT date,temp FROM data_set1 WHERE date >= '".$date." 00:00:00' AND date <= '".$date." 23:59:59' AND location = ". $location." ORDER BY date ASC ";
        $result = mysqli_query($conn,$mysql_qry);
        $myJSON="";
        if(mysqli_num_rows($result) > 0){
            $ret = array();
            $no=1;
            //first data are title heads
            $head=array();
            $head[0] = "Time";
            $head[1] = "Temperature"; 
            $ret[0] = $head;
            while($row = $result->fetch_assoc()){
                $arr=array();
                $pieces = explode(" ", $row["date"]);
                $arr[0] = $pieces[1];
                $arr[1] = intval($row["temp"]);
                $ret[$no] = $arr;
                $no++;
            }
            $myObj = new \stdClass();
            $myObj->new_update_time = $server_updated_time;
            $myObj->array = $ret;
            $myJSON = json_encode($myObj);
        }else{
            echo "0";
        }
        return $myJSON;
    }
}
?>
