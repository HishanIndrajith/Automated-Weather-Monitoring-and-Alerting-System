<?php
/*This server side php returns a json array abject containing the location data. To be loaded when app loads first time.
The architecture is an array of JSON objects for each location.

Author:Hishan Indrajith ** 
University of Peradeniya **
hishan.indrajith.95hia@gmail.com **
17-03-2018

Technologies used - PHP5, JSON, MYSQL
*/?>
      <?php
        require "conn.php";
        $mysql_qry = "SELECT * FROM location";
        $result = mysqli_query($conn,$mysql_qry);
        if(mysqli_num_rows($result) > 0){
            $ret = array();
            $no=0;
        	while($row = $result->fetch_assoc()){
                $id = $row["id"];
                $name = $row["name"];
                $latitude = $row["latitude"];
                $longitude = $row["longitude"];
                $myObj = new \stdClass();
                $myObj->id = $id;
                $myObj->name = $name;
                $myObj->latitude = $latitude;
                $myObj->longitude = $longitude;

                $ret[$no] = $myObj;
                $no++;
        	}
            echo json_encode($ret);
        }else{
        	echo "0";
        }
        mysqli_close($conn);

      ?>
