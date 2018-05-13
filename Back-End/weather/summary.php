<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////   *                *               *                 Author:Hishan Indrajith           *         *             *           *           ////
////          *                *             *            University of Peradeniya                                                          ////
////                                                 hishan.indrajith.95hia@gmail.com         *             *            *                  ////   
////      *              *             *                        17-03-2018             *              *             *            *          ////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////                                                                                             /////////////////////////
//////////////////////////     PROJECT - NETWORKED AND AUTOMATED WEATHER MONITORING AND ALERTING SYSTEM - GROUP 04     /////////////////////////
//////////////////////////                                                                                             /////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/*Long Polling PHP server script for live temperature and humidity updates.
This server side php returns a json array abject containing the new temperature and humidity updates at the database. 
The script uses long polling technology.
The last time of update is saved in a file in server. clients must send a HTTP GET request containing the last time client was updated as soon as an update happen using AJAX.
This script waits for a new server update and notify the client through a JSON object containing new data and the updated time.
So front end will be updated with live data.
Technologies used - PHP5, JSON, MYSQL, Long Polling Concept
*/
ini_set('max_execution_time', 0); // maximum execution time is set unlimited
require "conn.php";
$script_start_time=time();
echo poll($_GET['summary_last_updated_time'],$conn);
mysqli_close($conn);
function poll($summary_last_updated_time,$conn){
    // 
    $server_updated_time = file_get_contents('summary.txt');
    //
    if($server_updated_time == $summary_last_updated_time){
        sleep(1); 
        if(time()<$GLOBALS['script_start_time']+600){ //stop script after 10 minutes
           return poll($summary_last_updated_time,$conn);
        }else{
            return '0';
        }
        
    }else{
        date_default_timezone_set("Asia/Colombo");
        $date = date("Y-m-d");
        $location_count = intval(file_get_contents('locations/location_count.txt'));
        $mysql_qry = "SELECT * FROM data_set1 WHERE ";
        for ($x = 0; $x < $location_count; $x++) {
            $mysql_qry .= "date = (SELECT MAX(date) FROM data_set1 WHERE location =" .($x+1).") AND location =".($x+1);
            if($x+1!=$location_count){
                $mysql_qry .= " OR ";
            }
        }
        $result = mysqli_query($conn,$mysql_qry);
        $myJSON="";
        if(mysqli_num_rows($result) > 0){
            $ret = array();
            $no=0;
            while($row = $result->fetch_assoc()){
                $myObj = new \stdClass();
                $myObj->temp = $row["temp"];
                $myObj->hum = $row["hum"];
                $myObj->wind_speed = $row["wind_speed"];
                $myObj->is_raining = $row["is_raining"];
                $myObj->current_rainfall = $row["current_rainfall"];
                $myObj->location = $row["location"];
                $ret[$no] = $myObj;
                $no++;
            }
            $mainObj = new \stdClass();
            $mainObj->new_update_time = $server_updated_time;
            $mainObj->array = $ret;
            $myJSON = json_encode($mainObj);
        }else{
            echo "0";
        }
        return $myJSON;
    }
}
?>
