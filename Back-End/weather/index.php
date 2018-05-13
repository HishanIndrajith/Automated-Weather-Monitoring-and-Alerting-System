<!--
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
This is the main server side php script.
Author:Hishan Indrajith ** 
hishan.indrajith.95hia@gmail.com **
17-03-2018

Technologies used - PHP5, JSON, MYSQL, AJAX, Java Script
-->

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Weather Online</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="shortcut icon" href="icons/icon.png" />
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <script src="js/jquery.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script type="text/javascript" src="gstatic/loader.js"></script>
  <script type="text/javascript">
    //load locations 
    var locations;
    var selected_location_id = 0;
    var th_last_updated_time="0000-00-00 00:00:00"; //last updated time of temperature and humidty
    var summary_last_updated_time="0000-00-00 00:00:00";//last updated time of summary
    //temp chart array
    var temp_chart = [["time","temperature (°C)"]];
    //hum chart array
    var hum_chart = [["time","humidity"]];
    //wind speed chart array
    var wind_chart = [["time","wind speed (kmph)"]];
    //rainfall chart array
    var rain_chart = [["time","rainfall (mm)"]];
    //maps
    var map1; //Rain Status Map
    var map2; //Extreme Weather Map
    //markers for rain map
    var markers_rain = [];
    //markers for extreme map
    var markers_extreme = [];
    //extreme case limits
    var temp_min=10;
    var temp_max=40;
    var rain_max=100;
    var wind_max=130;
    //google charts
    google.charts.load('current', {'packages':['corechart']});
    //alert notification is desplayed array
    var alert_is_displayed = [];
    function initFunction(){

      //ajex request to get location details
      var ajaxRequest; //create ajax request
      try {      
        // Opera 8.0+, Firefox, Safari      
        ajaxRequest = new XMLHttpRequest();
      } catch (e) {         
        // Internet Explorer Browsers
        try {
          ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
          try {
            ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
          } catch (e) {
            alert("Your browser not good. Use a newer browser!");
            return false;
          }
        }
      } 
      ajaxRequest.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
          locations = JSON.parse(this.responseText); //assign the data array when received
           //search suggestion add to ul
          var list = document.getElementById("location_list");
          var listHTML = "";
          for (i = 0; i < locations.length; i++) {
            listHTML += "<option value=\""+locations[i].name+"\">";
            //set alert displayed to non initially for all locations just after location array received.i is location-1
            alert_is_displayed[i] = {temp_high:-100, temp_low:-100, rain:-100, wind:-100};
          }
          list.innerHTML=listHTML;
          //search suggestion add over
          //load summmary
          loadSummary();
        }
      };
      ajaxRequest.open("GET", "locations.php", true);
      ajaxRequest.send();
      //load locations code over
      //load years in minimum-maximum charts
      var currentYear = new Date().getFullYear();
      var temp_year_select = document.getElementById("temp_year_select");
      var hum_year_select = document.getElementById("hum_year_select");
      var wind_year_select = document.getElementById("wind_year_select");
      var rain_year_select = document.getElementById("rain_year_select");
      var options="";
      for(year = 2005; year <= currentYear; year++){
        options+="<option>"+year+"</option>";
      }
      temp_year_select.innerHTML=options;
      hum_year_select.innerHTML=options;
      wind_year_select.innerHTML=options;
      rain_year_select.innerHTML=options;
      //load years in minimum-maximum charts over
      //load select location message,image in board
      document.getElementById('temp_curve_chart').innerHTML=
            "<img style=\"display: block;margin-left: auto;margin-right: auto;margin-top: 100px\" src=\"icons\\location.png\" alt=\"No Data\" width=\"256\" height=\"256\">"+
            "<h2 style=\"text-align: center\">Select the location to View Charts</h2>";
      document.getElementById('hum_curve_chart').innerHTML=
            "<img style=\"display: block;margin-left: auto;margin-right: auto;margin-top: 100px\" src=\"icons\\location.png\" alt=\"No Data\" width=\"256\" height=\"256\">"+
            "<h2 style=\"text-align: center\">Select the location to View Charts</h2>";
      document.getElementById('wind_curve_chart').innerHTML=
            "<img style=\"display: block;margin-left: auto;margin-right: auto;margin-top: 100px\" src=\"icons\\location.png\" alt=\"No Data\" width=\"256\" height=\"256\">"+
            "<h2 style=\"text-align: center\">Select the location to View Charts</h2>";
      document.getElementById('rain_curve_chart').innerHTML=
            "<img style=\"display: block;margin-left: auto;margin-right: auto;margin-top: 100px\" src=\"icons\\location.png\" alt=\"No Data\" width=\"256\" height=\"256\">"+
            "<h2 style=\"text-align: center\">Select the location to View Charts</h2>";

      document.getElementById('temp_chart_by_date').innerHTML=
            "<img style=\"display: block;margin-left: auto;margin-right: auto;margin-top: 100px\" src=\"icons\\location.png\" alt=\"No Data\" width=\"256\" height=\"256\">"+
            "<h2 style=\"text-align: center\">Select the location and date to View Charts</h2>";
      document.getElementById('hum_chart_by_date').innerHTML=
            "<img style=\"display: block;margin-left: auto;margin-right: auto;margin-top: 100px\" src=\"icons\\location.png\" alt=\"No Data\" width=\"256\" height=\"256\">"+
            "<h2 style=\"text-align: center\">Select the location and date to View Charts</h2>";
      document.getElementById('rain_chart_by_date').innerHTML=
            "<img style=\"display: block;margin-left: auto;margin-right: auto;margin-top: 100px\" src=\"icons\\location.png\" alt=\"No Data\" width=\"256\" height=\"256\">"+
            "<h2 style=\"text-align: center\">Select the location and date to View Charts</h2>";
      document.getElementById('wind_chart_by_date').innerHTML=
            "<img style=\"display: block;margin-left: auto;margin-right: auto;margin-top: 100px\" src=\"icons\\location.png\" alt=\"No Data\" width=\"256\" height=\"256\">"+
            "<h2 style=\"text-align: center\">Select the location and date to View Charts</h2>";

      document.getElementById('temp_chart_annual').innerHTML=
            "<img style=\"display: block;margin-left: auto;margin-right: auto;margin-top: 100px\" src=\"icons\\location.png\" alt=\"No Data\" width=\"256\" height=\"256\">"+
            "<h2 style=\"text-align: center\">Select the location and year to View Charts</h2>";
      document.getElementById('hum_chart_annual').innerHTML=
            "<img style=\"display: block;margin-left: auto;margin-right: auto;margin-top: 100px\" src=\"icons\\location.png\" alt=\"No Data\" width=\"256\" height=\"256\">"+
            "<h2 style=\"text-align: center\">Select the location and year to View Charts</h2>";
      document.getElementById('wind_chart_annual').innerHTML=
            "<img style=\"display: block;margin-left: auto;margin-right: auto;margin-top: 100px\" src=\"icons\\location.png\" alt=\"No Data\" width=\"256\" height=\"256\">"+
            "<h2 style=\"text-align: center\">Select the location and year to View Charts</h2>";
      document.getElementById('rain_chart_annual').innerHTML=
            "<img style=\"display: block;margin-left: auto;margin-right: auto;margin-top: 100px\" src=\"icons\\location.png\" alt=\"No Data\" width=\"256\" height=\"256\">"+
            "<h2 style=\"text-align: center\">Select the location and year to View Charts</h2>";
    }
    //function when location selected
    function location_selected(){
      var location_txt = document.getElementById("location_txt");
      var location_value = document.getElementById("location_input").value;
      location_txt.innerHTML="<span class=\"glyphicon glyphicon-map-marker\"> "+location_value+"</span>";
      for (i = 0; i < locations.length; i++) {
        if(locations[i].name==location_value){
          selected_location_id=locations[i].id;
        }
      }
      //must load chart now
      loadChart();
    }

    function temp_chart_by_day(){
      if(selected_location_id==0){
        alert("Location not selected");
        return;
      }
      var date_selected = document.getElementById("date_temp").value;
      var ajaxRequest;
      try {      
        // Opera 8.0+, Firefox, Safari      
        ajaxRequest = new XMLHttpRequest();
      } catch (e) {         
        // Internet Explorer Browsers
        try {
          ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
          try {
            ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
          } catch (e) {
            alert("Your browser not good. Use a newer browser!");
            return false;
          }
        }
      } 
      ajaxRequest.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
          if(this.responseText != "0"){
            var dataArray = JSON.parse(this.responseText);
            //draw charts - temperature
            var data = google.visualization.arrayToDataTable(dataArray);
            var options = {
            title: 'Temperature Variation on date '+date_selected,
            width:1000,
            height:600,
            pointSize:4,
            hAxis: { title: "time" ,textPosition: 'none' },
            vAxis: { title: "temperature (°C)" },
            colors: ['#15A0C8'],
            legend: {position: 'none'}
            };
            var chart = new google.visualization.AreaChart(document.getElementById('temp_chart_by_date'));
            chart.draw(data, options);
            //drawing over -temperatures
          }else{
            document.getElementById('temp_chart_by_date').innerHTML=
            "<img style=\"display: block;margin-left: auto;margin-right: auto;margin-top: 100px\" src=\"icons\\nodata.png\" alt=\"No Data\" width=\"256\" height=\"256\">"+
            "<h2 style=\"text-align: center\">No Data Available for this Date</h2>"
            ;
          }
        }
      };
      ajaxRequest.open("GET", "tempChartByDate.php?date_selected="+date_selected+"&location="+selected_location_id, true);
      ajaxRequest.send();
    }

    function hum_chart_by_day(){
      if(selected_location_id==0){
        alert("Location not selected");
        return;
      }
      var date_selected = document.getElementById("date_hum").value;
      var ajaxRequest;
      try {      
        // Opera 8.0+, Firefox, Safari      
        ajaxRequest = new XMLHttpRequest();
      } catch (e) {         
        // Internet Explorer Browsers
        try {
          ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
          try {
            ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
          } catch (e) {
            alert("Your browser not good. Use a newer browser!");
            return false;
          }
        }
      } 
      ajaxRequest.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
          if(this.responseText != "0"){
            var dataArray = JSON.parse(this.responseText);
            //draw charts - humidity
            var data = google.visualization.arrayToDataTable(dataArray);
            var options = {
            title: 'Humidity Variation on date '+date_selected,
            width:1000,
            height:600,
            pointSize:4,
            hAxis: { title: "time" ,textPosition: 'none' },
            vAxis: { title: "Humidity" },
            colors: ['#f1ca3a'],
            legend: {position: 'none'}
            };
            var chart = new google.visualization.AreaChart(document.getElementById('hum_chart_by_date'));
            chart.draw(data, options);
            //drawing over -humidity
          }else{
            document.getElementById('hum_chart_by_date').innerHTML=
            "<img style=\"display: block;margin-left: auto;margin-right: auto;margin-top: 100px\" src=\"icons\\nodata.png\" alt=\"No Data\" width=\"256\" height=\"256\">"+
            "<h2 style=\"text-align: center\">No Data Available for this Date</h2>"
            ;
          }
        }
      };
      ajaxRequest.open("GET", "humChartByDate.php?date_selected="+date_selected+"&location="+selected_location_id, true);
      ajaxRequest.send();
    }

  function wind_chart_by_day(){
      if(selected_location_id==0){
        alert("Location not selected");
        return;
      }
      var date_selected = document.getElementById("date_wind").value;
      var ajaxRequest;
      try {      
        // Opera 8.0+, Firefox, Safari      
        ajaxRequest = new XMLHttpRequest();
      } catch (e) {         
        // Internet Explorer Browsers
        try {
          ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
          try {
            ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
          } catch (e) {
            alert("Your browser not good. Use a newer browser!");
            return false;
          }
        }
      } 
      ajaxRequest.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
          if(this.responseText != "0"){
            var dataArray = JSON.parse(this.responseText);
            //draw charts - humidity
            var data = google.visualization.arrayToDataTable(dataArray);
            var options = {
            title: 'wind Speed Variation on date '+date_selected,
            width:1000,
            height:600,
            pointSize:4,
            hAxis: { title: "time" ,textPosition: 'none' },
            vAxis: { title: "Wind Speed" },
            colors: ['#43459d'],
            legend: {position: 'none'}
            };
            var chart = new google.visualization.AreaChart(document.getElementById('wind_chart_by_date'));
            chart.draw(data, options);
            //drawing over -humidity
          }else{
            document.getElementById('wind_chart_by_date').innerHTML=
            "<img style=\"display: block;margin-left: auto;margin-right: auto;margin-top: 100px\" src=\"icons\\nodata.png\" alt=\"No Data\" width=\"256\" height=\"256\">"+
            "<h2 style=\"text-align: center\">No Data Available for this Date</h2>"
            ;
          }
        }
      };
      ajaxRequest.open("GET", "windChartByDate.php?date_selected="+date_selected+"&location="+selected_location_id, true);
      ajaxRequest.send();
    }

    function rain_chart_by_day(){
      if(selected_location_id==0){
        alert("Location not selected");
        return;
      }
      var date_selected = document.getElementById("date_rain").value;
      var ajaxRequest;
      try {      
        // Opera 8.0+, Firefox, Safari      
        ajaxRequest = new XMLHttpRequest();
      } catch (e) {         
        // Internet Explorer Browsers
        try {
          ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
          try {
            ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
          } catch (e) {
            alert("Your browser not good. Use a newer browser!");
            return false;
          }
        }
      } 
      ajaxRequest.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
          if(this.responseText != "0"){
            var dataArray = JSON.parse(this.responseText);
            //draw charts - humidity
            var data = google.visualization.arrayToDataTable(dataArray);
            var options = {
            title: 'rainfall Variation on date '+date_selected,
            width:1000,
            height:600,
            pointSize:4,
            hAxis: { title: "time" ,textPosition: 'none' },
            vAxis: { title: "Rainfall" },
            colors: ['#e49307'],
            legend: {position: 'none'}
            };
            var chart = new google.visualization.AreaChart(document.getElementById('rain_chart_by_date'));
            chart.draw(data, options);
            //drawing over -humidity
          }else{
            document.getElementById('rain_chart_by_date').innerHTML=
            "<img style=\"display: block;margin-left: auto;margin-right: auto;margin-top: 100px\" src=\"icons\\nodata.png\" alt=\"No Data\" width=\"256\" height=\"256\">"+
            "<h2 style=\"text-align: center\">No Data Available for this Date</h2>"
            ;
          }
        }
      };
      ajaxRequest.open("GET", "rainChartByDate.php?date_selected="+date_selected+"&location="+selected_location_id, true);
      ajaxRequest.send();
    }
//function to send ajax call to get annual summary
    function temp_chart_annual(){
      if(selected_location_id==0){
        alert("Location not selected");
        return;
      }
      var year = document.getElementById("temp_year_select").value;
      var ajaxRequest;
      try {      
        // Opera 8.0+, Firefox, Safari      
        ajaxRequest = new XMLHttpRequest();
      } catch (e) {         
        // Internet Explorer Browsers
        try {
          ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
          try {
            ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
          } catch (e) {
            alert("Your browser not good. Use a newer browser!");
            return false;
          }
        }
      } 
      ajaxRequest.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
          if(this.responseText != "0"){
            var dataArray = JSON.parse(this.responseText);
            //draw charts - temperature
            var data = google.visualization.arrayToDataTable(dataArray);
            var options = {
            title: 'Temperature Variation Annual Summary - '+year,
            width:1000,
            height:600,
            pointSize:4,
            hAxis: { title: "date" ,textPosition: 'none' },
            vAxis: { title: "temperature (°C)" },
            };
            var chart = new google.visualization.LineChart(document.getElementById('temp_chart_annual'));
            chart.draw(data, options);
            //drawing over -temperatures
          }else{
            document.getElementById('temp_chart_annual').innerHTML=
            "<img style=\"display: block;margin-left: auto;margin-right: auto;margin-top: 100px\" src=\"icons\\nodata.png\" alt=\"No Data\" width=\"256\" height=\"256\">"+
            "<h2 style=\"text-align: center\">No Data Available for this Year</h2>"
            ;
          }
        }
      };
      ajaxRequest.open("GET", "temp_chart_annual.php?year="+year+"&location="+selected_location_id, true);
      ajaxRequest.send();
    }

    function hum_chart_annual(){
      if(selected_location_id==0){
        alert("Location not selected");
        return;
      }
      var year = document.getElementById("hum_year_select").value;
      var ajaxRequest;
      try {      
        // Opera 8.0+, Firefox, Safari      
        ajaxRequest = new XMLHttpRequest();
      } catch (e) {         
        // Internet Explorer Browsers
        try {
          ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
          try {
            ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
          } catch (e) {
            alert("Your browser not good. Use a newer browser!");
            return false;
          }
        }
      } 
      ajaxRequest.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
          if(this.responseText != "0"){
            var dataArray = JSON.parse(this.responseText);
            //draw charts - temperature
            var data = google.visualization.arrayToDataTable(dataArray);
            var options = {
            title: 'Humidity Variation Annual Summary - '+year,
            width:1000,
            height:600,
            pointSize:4,
            hAxis: { title: "date" ,textPosition: 'none' },
            vAxis: { title: "humidity ()" },
            };
            var chart = new google.visualization.LineChart(document.getElementById('hum_chart_annual'));
            chart.draw(data, options);
            //drawing over -temperatures
          }else{
            document.getElementById('hum_chart_annual').innerHTML=
            "<img style=\"display: block;margin-left: auto;margin-right: auto;margin-top: 100px\" src=\"icons\\nodata.png\" alt=\"No Data\" width=\"256\" height=\"256\">"+
            "<h2 style=\"text-align: center\">No Data Available for this Year</h2>"
            ;
          }
        }
      };
      ajaxRequest.open("GET", "hum_chart_annual.php?year="+year+"&location="+selected_location_id, true);
      ajaxRequest.send();
    }

    function wind_chart_annual(){
      if(selected_location_id==0){
        alert("Location not selected");
        return;
      }
      var year = document.getElementById("wind_year_select").value;
      var ajaxRequest;
      try {      
        // Opera 8.0+, Firefox, Safari      
        ajaxRequest = new XMLHttpRequest();
      } catch (e) {         
        // Internet Explorer Browsers
        try {
          ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
          try {
            ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
          } catch (e) {
            alert("Your browser not good. Use a newer browser!");
            return false;
          }
        }
      } 
      ajaxRequest.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
          if(this.responseText != "0"){
            var dataArray = JSON.parse(this.responseText);
            //draw charts - temperature
            var data = google.visualization.arrayToDataTable(dataArray);
            var options = {
            title: 'Wind Speed Variation Annual Summary - '+year,
            width:1000,
            height:600,
            pointSize:4,
            hAxis: { title: "date" ,textPosition: 'none' },
            vAxis: { title: "wind speed (kmph)" },
            };
            var chart = new google.visualization.LineChart(document.getElementById('wind_chart_annual'));
            chart.draw(data, options);
            //drawing over -temperatures
          }else{
            document.getElementById('wind_chart_annual').innerHTML=
            "<img style=\"display: block;margin-left: auto;margin-right: auto;margin-top: 100px\" src=\"icons\\nodata.png\" alt=\"No Data\" width=\"256\" height=\"256\">"+
            "<h2 style=\"text-align: center\">No Data Available for this Year</h2>"
            ;
          }
        }
      };
      ajaxRequest.open("GET", "wind_chart_annual.php?year="+year+"&location="+selected_location_id, true);
      ajaxRequest.send();
    }

    function rain_chart_annual(){
      if(selected_location_id==0){
        alert("Location not selected");
        return;
      }
      var year = document.getElementById("rain_year_select").value;
      var ajaxRequest;
      try {      
        // Opera 8.0+, Firefox, Safari      
        ajaxRequest = new XMLHttpRequest();
      } catch (e) {         
        // Internet Explorer Browsers
        try {
          ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
          try {
            ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
          } catch (e) {
            alert("Your browser not good. Use a newer browser!");
            return false;
          }
        }
      } 
      ajaxRequest.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
          if(this.responseText != "0"){
            var dataArray = JSON.parse(this.responseText);
            //draw charts - temperature
            var data = google.visualization.arrayToDataTable(dataArray);
            var options = {
            title: 'Rainfall Variation Annual Summary - '+year,
            width:1000,
            height:600,
            pointSize:4,
            hAxis: { title: "date" ,textPosition: 'none' },
            vAxis: { title: "rainfall (mm)" },
            };
            var chart = new google.visualization.LineChart(document.getElementById('rain_chart_annual'));
            chart.draw(data, options);
            //drawing over -temperatures
          }else{
            document.getElementById('rain_chart_annual').innerHTML=
            "<img style=\"display: block;margin-left: auto;margin-right: auto;margin-top: 100px\" src=\"icons\\nodata.png\" alt=\"No Data\" width=\"256\" height=\"256\">"+
            "<h2 style=\"text-align: center\">No Data Available for this Year</h2>"
            ;
          }
        }
      };
      ajaxRequest.open("GET", "rain_chart_annual.php?year="+year+"&location="+selected_location_id, true);
      ajaxRequest.send();
    }
    //load Summary assynchronously and keep live
    function loadSummary() {
      var ajaxRequest;
      try {      
        // Opera 8.0+, Firefox, Safari      
        ajaxRequest = new XMLHttpRequest();
      } catch (e) {         
        // Internet Explorer Browsers
        try {
          ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
          try {
            ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
          } catch (e) {
            alert("Your browser not good. Use a newer browser!");
            return false;
          }
        }
      } 
      ajaxRequest.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
          if(this.responseText != "0"){
            var myObj = JSON.parse(this.responseText);
            //update the client summary flag
            summary_last_updated_time = myObj.new_update_time;
            var summary_data_array = myObj.array;
            var table = document.getElementById("table");
            var HTML = "";
            this.responseText;
            for (i = 0; i < summary_data_array.length; i++) {
              HTML += "<tr><td>" + summary_data_array[i].location + "</td><td>"
              + summary_data_array[i].temp + "</td><td>"
              + summary_data_array[i].hum + "</td><td>"
              + summary_data_array[i].wind_speed + "</td></tr>";
            }
            table.innerHTML =HTML;
            setMarkers1(map1,summary_data_array);
            setMarkers2(map2,summary_data_array);
          }
          loadSummary();
        }
      };
      ajaxRequest.open("GET", "summary.php?summary_last_updated_time="+summary_last_updated_time, true);
      ajaxRequest.send();
    }

    function loadChart() {
      var ajaxRequest;
      try {      
        // Opera 8.0+, Firefox, Safari      
        ajaxRequest = new XMLHttpRequest();
      } catch (e) {         
        // Internet Explorer Browsers
        try {
          ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
          try {
            ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
          } catch (e) {
            alert("Your browser not good. Use a newer browser!");
            return false;
          }
        }
      } 
      ajaxRequest.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
          if(this.responseText != "0"){
            var myObj = JSON.parse(this.responseText);
            //update the client flag
            th_last_updated_time = myObj.new_update_time;
            //update the google chart
            var temp_data_array = myObj.arrayTemp;
            temp_chart= temp_chart.concat(temp_data_array);
            temp_data_array = myObj.arrayHum;
            hum_chart= hum_chart.concat(temp_data_array);
            temp_data_array = myObj.arrayWind;
            wind_chart= wind_chart.concat(temp_data_array);
            temp_data_array = myObj.arrayRain;
            rain_chart= rain_chart.concat(temp_data_array);
            //draw charts - temperature
            var data1 = google.visualization.arrayToDataTable(temp_chart);
            var date = new Date();
            var options1 = {
            title: 'Temperature Variation Today ('+date.getFullYear() + " - " + (date.getMonth()+1) + " - " + date.getDate()+")",
            width:1000,
            height:600,
            pointSize:5,
            hAxis: { title: "time" ,textPosition: 'none' },
            vAxis: { title: "temperature (°C)" },
            colors: ['#15A0C8'],
            legend: {position: 'none'}
            };
            var chart1 = new google.visualization.AreaChart(document.getElementById('temp_curve_chart'));
            chart1.draw(data1, options1);
            //drawing over -temperature
            //draw charts - humidity
            var data2 = google.visualization.arrayToDataTable(hum_chart);
            var options2 = {
            title: 'Humidity Variation Today ('+date.getFullYear() + " - " + (date.getMonth()+1) + " - " + date.getDate()+")",
            width:1000,
            height:600,
            pointSize:5,
            hAxis: { title: "time" ,textPosition: 'none' },
            vAxis: { title: "Humidity " },
            colors: ['#f1ca3a'],
            legend: {position: 'none'}
            };
            var chart2 = new google.visualization.AreaChart(document.getElementById('hum_curve_chart'));
            chart2.draw(data2, options2);
            //drawing over -humidity
            //draw charts - wind speed
            var data3 = google.visualization.arrayToDataTable(wind_chart);
            var options3 = {
            title: 'Wind Speed Variation Today ('+date.getFullYear() + " - " + (date.getMonth()+1) + " - " + date.getDate()+")",
            width:1000,
            height:600,
            pointSize:5,
            hAxis: { title: "time" ,textPosition: 'none' },
            vAxis: { title: "Wind Speed (kmph)" },
            colors: ['#43459d'],
            legend: {position: 'none'}
            };
            var chart3 = new google.visualization.AreaChart(document.getElementById('wind_curve_chart'));
            chart3.draw(data3, options3);
            //drawing over - wind speed
            //draw charts - rainfall
            var data4 = google.visualization.arrayToDataTable(rain_chart);
            var options4 = {
            title: 'RainFall Variation Today ('+date.getFullYear() + " - " + (date.getMonth()+1) + " - " + date.getDate()+")",
            width:1000,
            height:600,
            pointSize:5,
            hAxis: { title: "time" ,textPosition: 'none' },
            vAxis: { title: "RainFall (mm)" },
            colors: ['#e49307'],
            legend: {position: 'none'}
            };
            var chart4 = new google.visualization.AreaChart(document.getElementById('rain_curve_chart'));
            chart4.draw(data4, options4);
            //drawing over -rainfall
            
          }else{
            document.getElementById('temp_curve_chart').innerHTML=
            "<img style=\"display: block;margin-left: auto;margin-right: auto;margin-top: 100px\" src=\"icons\\nodata.png\" alt=\"No Data\" width=\"256\" height=\"256\">"+
            "<h2 style=\"text-align: center\">No Data Available Yet. Waiting...</h2>";
            document.getElementById('hum_curve_chart').innerHTML=
            "<img style=\"display: block;margin-left: auto;margin-right: auto;margin-top: 100px\" src=\"icons\\nodata.png\" alt=\"No Data\" width=\"256\" height=\"256\">"+
            "<h2 style=\"text-align: center\">No Data Available Yet. Waiting...</h2>";
            document.getElementById('wind_curve_chart').innerHTML=
            "<img style=\"display: block;margin-left: auto;margin-right: auto;margin-top: 100px\" src=\"icons\\nodata.png\" alt=\"No Data\" width=\"256\" height=\"256\">"+
            "<h2 style=\"text-align: center\">No Data Available Yet. Waiting...</h2>";
            document.getElementById('rain_curve_chart').innerHTML=
            "<img style=\"display: block;margin-left: auto;margin-right: auto;margin-top: 100px\" src=\"icons\\nodata.png\" alt=\"No Data\" width=\"256\" height=\"256\">"+
            "<h2 style=\"text-align: center\">No Data Available Yet. Waiting...</h2>";
          }
          loadChart();
        }
      };
      ajaxRequest.open("GET", "tempchart1.php?th_last_updated_time="+th_last_updated_time+"&location="+selected_location_id, true);
      ajaxRequest.send();
    }

    //load the map
    function myMap() {
      var myCenter = new google.maps.LatLng(7.754613, 80.591325);
      var mapProp = {center:myCenter, zoom:7.5, scrollwheel:false, draggable:false, mapTypeId:google.maps.MapTypeId.TERRAIN};
      map1 = new google.maps.Map(document.getElementById("googleMap1"),mapProp);
      map2 = new google.maps.Map(document.getElementById("googleMap2"),mapProp);

    }
    function setMarkers1(map,summary_data_array) {
        // Adds markers to the map.

        // Marker sizes are expressed as a Size of X,Y where the origin of the image
        // (0,0) is located in the top left of the image.

        // Origins, anchor positions and coordinates of the marker increase in the X
        // direction to the right and in the Y direction down.
        var image0 = {
          url: 'icons/rain.png',
          // This marker is 20 pixels wide by 32 pixels high.
          size: new google.maps.Size(41, 41),
          // The origin for this image is (0, 0).
          origin: new google.maps.Point(0, 0),
          // The anchor for this image is the base of the flagpole at (0, 32).
          anchor: new google.maps.Point(0, 0)
        };
        var image1 = {
          url: 'icons/sun.png',
          // This marker is 41 pixels wide by 41 pixels high.
          size: new google.maps.Size(41, 41),
          // The origin for this image is (0, 0).
          origin: new google.maps.Point(0, 0),
          // The anchor for this image is the middle 20,20.
          anchor: new google.maps.Point(20, 20)
        };
        // Shapes define the clickable region of the icon. The type defines an HTML
        // <area> element 'poly' which traces out a polygon as a series of X,Y points.
        // The final coordinate closes the poly by connecting to the first coordinate.
        var shape = {
          coords: [1, 1, 1, 20, 18, 20, 18, 1],
          type: 'poly'
        };
        for (var i = 0; i < summary_data_array.length; i++) {
          var image = summary_data_array[i].is_raining==0?image1:image0;
          var latitude;
          var longitude;
          var location_name;
              
          //if ordered in sql no need to loop ,the id's might be same.. check it and implement later
          for (j = 0; j < locations.length; j++) {
            if(locations[j].id==summary_data_array[i].location){
              latitude=locations[j].latitude;
              longitude=locations[j].longitude;
              location_name=locations[j].name;
            }
          }

          var marker = new google.maps.Marker({
            position: {lat: parseFloat(latitude), lng: parseFloat(longitude)},
            icon: image,
            shape: shape,
            title: location_name +"\n"+(summary_data_array[i].is_raining==0?"not Raining":"Raining"),
            zIndex:parseInt(0)
          });
          

          if(markers_rain[i]!=null){
            markers_rain[i].setMap(null);
          }
          marker.setMap(map);
          markers_rain[i]=marker;
        }

    }
    function setMarkers2(map,summary_data_array) {
        // Adds markers to the map.

        // Marker sizes are expressed as a Size of X,Y where the origin of the image
        // (0,0) is located in the top left of the image.

        // Origins, anchor positions and coordinates of the marker increase in the X
        // direction to the right and in the Y direction down.
        var red = {
          url: 'icons/red.png',
          // This marker is 40 pixels wide by 40 pixels high.
          size: new google.maps.Size(20, 20),
          // The origin for this image is (0, 0).
          origin: new google.maps.Point(0, 0),
          // The anchor for this image is the middle 20,20.
          anchor: new google.maps.Point(10, 10)
        };
        var green = {
          url: 'icons/green.png',
          // This marker is 20 pixels wide by 32 pixels high.
          size: new google.maps.Size(20, 20),
          // The origin for this image is (0, 0).
          origin: new google.maps.Point(0, 0),
          // The anchor for this image is the base of the flagpole at (0, 32).
          anchor: new google.maps.Point(10, 10)
        };
        // Shapes define the clickable region of the icon. The type defines an HTML
        // <area> element 'poly' which traces out a polygon as a series of X,Y points.
        // The final coordinate closes the poly by connecting to the first coordinate.
        var shape = {
          coords: [1, 1, 1, 20, 18, 20, 18, 1],
          type: 'poly'
        };
        for (var i = 0; i < summary_data_array.length; i++) {
          var is_extream = summary_data_array[i].temp>=temp_max || summary_data_array[i].temp<=temp_min || summary_data_array[i].wind_speed>=wind_max || summary_data_array[i].current_rainfall>=rain_max ;
          var image = is_extream?red:green;
          var latitude;
          var longitude;
          var location_name;
          var location_id;
          //if ordered in sql no need to loop ,the id's might be same.. check it and implement later
          for (j = 0; j < locations.length; j++) {
            if(locations[j].id==summary_data_array[i].location){
              location_id=locations[j].id;
              latitude=locations[j].latitude;
              longitude=locations[j].longitude;
              location_name=locations[j].name;
            }
          }
          var marker = new google.maps.Marker({
            position: {lat: parseFloat(latitude), lng: parseFloat(longitude)},
            icon: image,
            shape: shape,
            title: location_name,
            zIndex:parseInt(0)
          });
          if(markers_extreme[i]!=null){
            markers_extreme[i].setMap(null);
          }
          marker.setMap(map);
          markers_extreme[i]=marker;

          //add notification
          if(is_extream){
            if(summary_data_array[i].temp>=temp_max && alert_is_displayed[location_id-1].temp_high!=summary_data_array[i].temp){
              var message = "<div class=\"alert alert-danger alert-dismissible fade in\"><a href=\"#\" class=\"close\" data-dismiss=\"alert\" aria-label=\"close\">&times;</a><strong>weather Alert!</strong> "+
              location_name+" has high temperature of " +summary_data_array[i].temp+ " C<sup>o</sup> exeeding the limit of "+temp_max+
              " C<sup>o</sup></div>";
              document.getElementById("alert-panel").innerHTML=message+document.getElementById("alert-panel").innerHTML;
              alert_is_displayed[location_id-1].temp_high=summary_data_array[i].temp;
              document.getElementById("beep").play();
            }
            if(summary_data_array[i].temp<=temp_min && alert_is_displayed[location_id-1].temp_low!=summary_data_array[i].temp){
              var message = "<div class=\"alert alert-danger alert-dismissible fade in\"><a href=\"#\" class=\"close\" data-dismiss=\"alert\" aria-label=\"close\">&times;</a><strong>weather Alert!</strong> "+
              location_name+" has low temperature of " +summary_data_array[i].temp+ " C<sup>o</sup> below the limit of "+temp_min+
              " C<sup>o</sup></div>";
              document.getElementById("alert-panel").innerHTML=message+document.getElementById("alert-panel").innerHTML;
              alert_is_displayed[location_id-1].temp_low=summary_data_array[i].temp;
              document.getElementById("beep").play();
            }
            if(summary_data_array[i].wind_speed>=wind_max && alert_is_displayed[location_id-1].wind!=summary_data_array[i].wind_speed){
              var message = "<div class=\"alert alert-danger alert-dismissible fade in\"><a href=\"#\" class=\"close\" data-dismiss=\"alert\" aria-label=\"close\">&times;</a><strong>weather Alert!</strong> "+
              location_name+" has high wind with speed " +summary_data_array[i].wind_speed+ " kmph exeeding the limit of "+wind_max+
              " kmph</div>";
              document.getElementById("alert-panel").innerHTML=message+document.getElementById("alert-panel").innerHTML;
              alert_is_displayed[location_id-1].wind=summary_data_array[i].wind_speed;
              document.getElementById("beep").play();
            }
            if(summary_data_array[i].current_rainfall>=rain_max && alert_is_displayed[location_id-1].rain!=summary_data_array[i].current_rainfall){
              var message = "<div class=\"alert alert-danger alert-dismissible fade in\"><a href=\"#\" class=\"close\" data-dismiss=\"alert\" aria-label=\"close\">&times;</a><strong>weather Alert!</strong> "+
              location_name+" has high rainfall of " +summary_data_array[i].current_rainfall+ " mm exeeding the limit of "+rain_max+
              " mm</div>";
              document.getElementById("alert-panel").innerHTML=message+document.getElementById("alert-panel").innerHTML;
              alert_is_displayed[location_id-1].rain=summary_data_array[i].current_rainfall;
              document.getElementById("beep").play();      
            }
          }
        }
    }

    function live(isLive){
      var liveBtn = document.getElementById("livebtn");
      if(isLive==1){
        liveBtn.style.visibility = "visible"; 
      }else if(isLive==0){
        liveBtn.style.visibility = "hidden"; 
      }
    }

  </script>
  <!--script type="text/javascript">
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawChart);

      var update_no=0;
      var table="";
      //POLLING
      //setInterval(function(){ loadTable(); }, 3000);
      //POLLING OVER

      function drawChart(data) {
        var data = google.visualization.arrayToDataTable(data);

        var options = {
          title: 'Temperature Variation',
          width:1000,
          height:600,
          legend: { position: 'right' }
        };

        var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));

        chart.draw(data, options);
      }

      function loadTable() {
        var ajaxRequest;
        try {      
        // Opera 8.0+, Firefox, Safari      
        ajaxRequest = new XMLHttpRequest();
        } catch (e) {         
           // Internet Explorer Browsers
          try {
            ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
          } catch (e) {
            try {
              ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e) {
              alert("Your browser broke!");
              return false;
            }
          }
        } 
        ajaxRequest.onreadystatechange = function() {
          if (this.readyState == 4 && this.status == 200) {
            document.getElementById("table").innerHTML =
            this.responseText;
          }
        };
        ajaxRequest.open("GET", "localhost", true);
        ajaxRequest.send();
        loadChart();
      }

      function loadChart() {
        var ajaxRequest;
        try {      
        // Opera 8.0+, Firefox, Safari      
        ajaxRequest = new XMLHttpRequest();
        } catch (e) {         
           // Internet Explorer Browsers
          try {
            ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
          } catch (e) {
            try {
              ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e) {
              alert("Your browser broke!");
              return false;
            }
          }
        } 
        ajaxRequest.onreadystatechange = function() {
          if (this.readyState == 4 && this.status == 200) {
            var data = [];
            var response = this.responseText;
            var no = parseInt(response.split(",")[0]);
            data[0]=['Time','Temperature'];
            for(i=1;i<no+1;i++){
              data[i]=[response.split(",")[2*i-1],parseInt(response.split(",")[2*i])];
            }
            drawChart(data);
            
          }
        };
        ajaxRequest.open("GET", "localhost/weather/chart.php", true);
        ajaxRequest.send();
      }
    </script-->


</head>
<body onload="initFunction()">
  <audio id="beep">
    <source src="beep/beep.mp3" type="audio/mpeg">
    Your browser does not support the audio element.
  </audio>

 <nav class="navbar navbar-inverse">
  <div class="container-fluid">
    <div class="navbar-header">
      <a class="navbar-brand" href="">Weather Online</a>
    </div>
    <ul class="nav navbar-nav">
      <li class="active"><a data-toggle="tab" href="#summary" onclick="live(1)">Summary</a></li>
      <li class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#">Temperature
        <span class="caret"></span></a>
        <ul class="dropdown-menu">
          <li><a data-toggle="tab" href="#temp1" onclick="live(1)">Today's Chart</a></li>
          <li><a data-toggle="tab" href="#temp2" onclick="live(0)">Chart by day</a></li>
          <li><a data-toggle="tab" href="#temp3" onclick="live(0)">Annual Summary</a></li>
        </ul>
      </li>
      <li class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#">Humidity
        <span class="caret"></span></a>
        <ul class="dropdown-menu">
          <li><a data-toggle="tab" href="#hum1" onclick="live(1)">Today's Chart</a></li>
          <li><a data-toggle="tab" href="#hum2" onclick="live(0)">Chart by day</a></li>
          <li><a data-toggle="tab" href="#hum3" onclick="live(0)">Annual Summary</a></li>
        </ul>
      </li>
      <li class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#rain">RainFall
        <span class="caret"></span></a>
        <ul class="dropdown-menu">
          <li><a data-toggle="tab" href="#rain1" onclick="live(1)">Today's Chart</a></li>
          <li><a data-toggle="tab" href="#rain2" onclick="live(0)">Chart by day</a></li>
          <li><a data-toggle="tab" href="#rain3" onclick="live(0)">Annual Summary</a></li>
        </ul>
      </li>
      <li class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#wind">Wind Speed
        <span class="caret"></span></a>
        <ul class="dropdown-menu">
          <li><a data-toggle="tab" href="#wind1" onclick="live(1)">Today's Chart</a></li>
          <li><a data-toggle="tab" href="#wind2" onclick="live(0)">Chart by day</a></li>
          <li><a data-toggle="tab" href="#wind3" onclick="live(0)">Annual Summary</a></li>
        </ul>
      </li>
    </ul>

    <button type="button" style="margin-bottom: 10px" class="btn btn-success navbar-btn" onclick="loadTable()"><i class="glyphicon glyphicon-question-sign"></i> Help</button>

    <form class="navbar-form navbar-left" onsubmit="location_selected();return false">
      <div class="input-group">
        <input id="location_input" list="location_list" type="text" class="form-control" placeholder="Location">
        <datalist id="location_list"></datalist>
        <div class="input-group-btn">
          <button class="btn btn-default" type="submit">
            <i class="glyphicon glyphicon-ok"></i>
          </button>
        </div>
      </div> 
    </form>

    <button type="button"  id="livebtn" style="margin-bottom: 10px" class="btn btn-primary navbar-btn" onclick="loadTable()">LIVE</button>

    <ul class="nav navbar-nav navbar-right">
      <li><a id="location_txt"><span class="glyphicon glyphicon-map-marker"></span></a></li>
    </ul>

  </div>
</nav> 
<div class="container-fluid">
  <div class="tab-content">
    <div id="summary" class="tab-pane fade in active">
      <div style="width: 370px; height: 650px; float: left">
        <h4 class="bg-primary text-center" style="margin-top: 0px; margin-bottom: 0px;padding-top: 5px;padding-bottom: 5px"><strong>Rain Status Map</strong></h4>
        <div id="googleMap1" style="width: 370px; height: 600px"></div>
        <script async defer src="https://maps.googleapis.com/maps/api/js?key= AIzaSyBhKzEgxK41au23wGo_drmBzU1vk7txdhs &callback=myMap"></script>
      </div>
      <div style="width: 370px; height: 650px; float: left; margin-left: 10px">
        <h4 class="bg-primary text-center" style="margin-top: 0px; margin-bottom: 0px;padding-top: 5px;padding-bottom: 5px"><strong>Extreme Weather Map</strong></h4>
        <div id="googleMap2" style="width: 370px; height: 600px"></div>
        <script async defer src="https://maps.googleapis.com/maps/api/js?key= AIzaSyBhKzEgxK41au23wGo_drmBzU1vk7txdhs &callback=myMap"></script>
      </div>
      <div style="width: 350px; float: left; margin-left: 10px">
          <table class="table table-dark table-hover table-striped table-bordered">
            <thead>
              <tr>
                <th>location</th>
                <th>temp (C<sup>o</sup>)</th>
                <th>hum</th>
                <th>wind (kmph)</th>
              </tr>
            </thead>
            <tbody id="table">
            </tbody>
          </table>
      </div>
    </div>
    <div id="temp1" class="tab-pane fade">
      <div style="width: 1000px; height: 650px; float: left" id="temp_curve_chart" ></div>     
    </div>
    <div id="temp2" class="tab-pane fade">
      <div style="margin-left:100px; width:600px; float: left">
        <div style="float: left"><h5>Choose Date : </h5></div>
        <input style="margin-left:10px; width: 300px; float: left" type="date" id="date_temp" class="form-control">
        <button type="button" class="btn btn-primary" onclick="temp_chart_by_day()">View</button>
      </div>
      <div style="width: 1000px; height: 500px; float: left" id="temp_chart_by_date" ></div>
    </div>
    <div id="temp3" class="tab-pane fade">
     <div style="margin-left:100px; width:600px; float: left" class="form-group">
        <div style="float: left"><h5>Select Year : </h5></div>
        <select id="temp_year_select" style="margin-left:10px; width: 300px; float: left" class="form-control"></select>
        <button type="button" class="btn btn-primary" onclick="temp_chart_annual()">View</button>
      </div>
      <div style="width: 1000px; height: 500px; float: left" id="temp_chart_annual" ></div>
    </div>

    <div id="hum1" class="tab-pane fade">
      <div style="width: 1000px; height: 650px; float: left" id="hum_curve_chart" ></div>     
    </div>
    <div id="hum2" class="tab-pane fade">
      <div style="margin-left:100px; width:600px; float: left">
        <div style="float: left"><h5>Choose Date : </h5></div>
        <input style="margin-left:10px; width: 300px; float: left" type="date" id="date_hum" class="form-control">
        <button type="button" class="btn btn-primary" onclick="hum_chart_by_day()">View</button>
      </div>
      <div style="width: 1000px; height: 500px; float: left" id="hum_chart_by_date" ></div>
    </div>
    <div id="hum3" class="tab-pane fade">
      <div style="margin-left:100px; width:600px; float: left" class="form-group">
        <div style="float: left"><h5>Select Year : </h5></div>
        <select id="hum_year_select" style="margin-left:10px; width: 300px; float: left" class="form-control"></select>
        <button type="button" class="btn btn-primary" onclick="hum_chart_annual()">View</button>
      </div>
      <div style="width: 1000px; height: 500px; float: left" id="hum_chart_annual" ></div> 
    </div>
    <div id="rain1" class="tab-pane fade">
      <div style="width: 1000px; height: 650px; float: left" id="rain_curve_chart" ></div>     
    </div>
    <div id="rain2" class="tab-pane fade">
      <div style="margin-left:100px; width:600px; float: left">
        <div style="float: left"><h5>Choose Date : </h5></div>
        <input style="margin-left:10px; width: 300px; float: left" type="date" id="date_rain" class="form-control">
        <button type="button" class="btn btn-primary" onclick="rain_chart_by_day()">View</button>
      </div>
      <div style="width: 1000px; height: 500px; float: left" id="rain_chart_by_date" ></div>
    </div>
    <div id="rain3" class="tab-pane fade">
      <div style="margin-left:100px; width:600px; float: left" class="form-group">
        <div style="float: left"><h5>Select Year : </h5></div>
        <select id="rain_year_select" style="margin-left:10px; width: 300px; float: left" class="form-control"></select>
        <button type="button" class="btn btn-primary" onclick="rain_chart_annual()">View</button>
      </div> 
      <div style="width: 1000px; height: 500px; float: left" id="rain_chart_annual" ></div> 
    </div>

    <div id="wind1" class="tab-pane fade">
      <div style="width: 1000px; height: 650px; float: left" id="wind_curve_chart" ></div>     
    </div>
    <div id="wind2" class="tab-pane fade">
      <div style="margin-left:100px; width:600px; float: left">
        <div style="float: left"><h5>Choose Date : </h5></div>
        <input style="margin-left:10px; width: 300px; float: left" type="date" id="date_wind" class="form-control">
        <button type="button" class="btn btn-primary" onclick="wind_chart_by_day()">View</button>
      </div>
      <div style="width: 1000px; height: 500px; float: left" id="wind_chart_by_date" ></div>
    </div>
    <div id="wind3" class="tab-pane fade">
      <div style="margin-left:100px; width:600px; float: left" class="form-group">
        <div style="float: left"><h5>Select Year : </h5></div>
        <select id="wind_year_select" style="margin-left:10px; width: 300px; float: left" class="form-control"></select>
        <button type="button" class="btn btn-primary" onclick="wind_chart_annual()">View</button>
      </div>
      <div style="width: 1000px; height: 500px; float: left" id="wind_chart_annual" ></div>  
    </div>
  </div>
  <!-- alert panel -->
  <div id="alert-panel" style="float: right; margin-left: 10px; width: 320px; height: 600px; overflow: auto;">
    <div class="alert alert-success alert-dismissible fade in">
      <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
      <strong>Hi User, Have a Good Day!</strong>
    </div>
  </div> 
</div>

</body>
</html>
