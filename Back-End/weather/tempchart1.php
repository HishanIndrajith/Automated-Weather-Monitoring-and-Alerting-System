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
$script_start_time=time();
echo poll($_GET['th_last_updated_time'],$conn);
mysqli_close($conn);
function poll($th_last_updated_time,$conn){
    
    $location = $_GET['location'];
    $server_updated_time = file_get_contents('locations/'. $location . '.txt');
    //
    if($server_updated_time == $th_last_updated_time){
        // 
        sleep(1);
        if(time()<$GLOBALS['script_start_time']+600){ //stop script after 10 minutes
            return poll($th_last_updated_time,$conn);
        }else{
            return '0';
        }
    }else{
        date_default_timezone_set("Asia/Colombo");
        $date = date("Y-m-d");
        $mysql_qry = "SELECT date,temp,hum,wind_speed,current_rainfall FROM data_set1 WHERE date >= '".$date." 00:00:00' AND date <= '".$date." 23:59:59' AND date > '".$th_last_updated_time."' AND location = ". $location." ORDER BY date ASC ";
        $result = mysqli_query($conn,$mysql_qry);
        $myJSON="";
        if(mysqli_num_rows($result) > 0){
            $ret1 = array();
            $ret2 = array();
            $ret3 = array();
            $ret4 = array();
            $no=0;
            while($row = $result->fetch_assoc()){
                //temperature
                $arr1=array();
                $pieces = explode(" ", $row["date"]);
                $arr1[0] = $pieces[1];
                $arr1[1] = intval($row["temp"]);
                $ret1[$no] = $arr1;

                //humidity
                $arr2=array();
                $arr2[0] = $pieces[1];
                $arr2[1] = intval($row["hum"]);
                $ret2[$no] = $arr2;

                //humidity
                $arr3=array();
                $arr3[0] = $pieces[1];
                $arr3[1] = intval($row["wind_speed"]);
                $ret3[$no] = $arr3;

                //humidity
                $arr4=array();
                $arr4[0] = $pieces[1];
                $arr4[1] = intval($row["current_rainfall"]);
                $ret4[$no] = $arr4;

                $no++;
            }
            $myObj = new \stdClass();
            $myObj->new_update_time = $server_updated_time;
            $myObj->arrayTemp = $ret1;
            $myObj->arrayHum = $ret2;
            $myObj->arrayWind = $ret3;
            $myObj->arrayRain = $ret4;
            $myJSON = json_encode($myObj);
        }else{
            echo "0";
        }
        return $myJSON;
    }
}
?>
