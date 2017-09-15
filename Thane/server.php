<!DOCTYPE html>
<html lang="en">
<head>
<title>TVWS Planning Tool</title>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<meta content="" name="description" />
<meta content="" name="author" />
<link rel="stylesheet" href="../config/css/bootstrap.min.css"/>
<link rel="stylesheet" href="../config/css/custom.css"/>
<link rel="icon" type="image/png" href="../config/images/antenna.png"/>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.2/jquery.min.js"></script>
</head>

<body>
<div class="navbar navbar-default navbar-static-top">
<div class="container-fluid">
<div class="navbar-header">
<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
<span class="icon-bar"></span>
</button>
<img src="../config/images/header.png" alt="HTML5 Icon" width="330" height="38"> <a class="navbar-brand" href="client.html"/><font color="black"><b></b></font></a>
<img src="../config/images/logo.gif" alt="HTML5 Icon" width="50" height="50"> <a class="navbar-brand" href="client.html"/><font color="black"><b></b></font></a>
<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Thane Model</b>
</div>
<div class="navbar-collapse collapse">
<ul class="nav navbar-nav navbar-right">
<li><a href="../London/client.html">London Model</a></li>
<li><a href="client.html">Home</a></li>
<li><a href="about.html">About</a></li>
<li><a href="readme.html">Readme</a></li>
</ul>
</div>
</div>
</div>

<div class="container-fluid">
<div class="row">
<div class="col-md-4">
<div class="well" id="leftbar">

<div class="alert alert-info" id="result_box" ><h4>Theoretical Model Simulation Executed</h4> </strong></div>

<script type="text/javascript">
    var h = $(window).height(),
    offsetTop = 105;
    $("#leftbar").css("height", (h - offsetTop));
</script>

<?php
if (isset($_POST["submit"]))
{
    if (isset($_FILES["upload"]))
    {
        if ($_FILES["upload"]["error"] > 0 && $_FILES["upload"]["error"]!=4)
        {
            echo "Error during file upload: " . $_FILES["upload"]["error"] . "<br />";
            echo "<button onclick=\"history.go(-1);\">Back </button>";
        }

        else
        {
            $storagename = "Thane_Original.csv";
            move_uploaded_file($_FILES["upload"]["tmp_name"], "./csv/" . $storagename);
        }
    }
}
echo "<hr>";
?>

<h4>General Specifications</h4>
<div class="row">
<ul>
Metric of measurement:&nbsp;&nbsp;
<label>
<?php
if(isset ($_POST["radio_choice"]))
{
    echo $_POST["radio_choice"];
}
?>
</label>
</ul>
</div>

<div class="row">
<ul>
Propagation Model:&nbsp;&nbsp;
<label>
<?php
if(isset ($_POST["submit"]))
{
    echo $_POST["propagation_model"];
}
?>
</label>
</ul>
</div>

<div class="row">
<ul>
Receiver Sensitivity (dBm):&nbsp;&nbsp;
<label>
<?php
if(isset ($_POST["rx_sensitivity"]))
{
    echo $_POST["rx_sensitivity"];
}
?>
</label>
</ul>
</div>

<div class="row">
<ul>
Transmit Power (dBm):&nbsp;&nbsp;
<label>
<?php
if(isset ($_POST["transmit_power"]))
{
    if((float)$_POST["transmit_power"] > 36)
        echo "36";
    else
        echo $_POST["transmit_power"];
}
?>
</label>
</ul>
</div>

<div class="row">
<ul>
TVWS Frequency (MHz):&nbsp;&nbsp;
<label>
<?php
if(isset ($_POST["frequency"]))
{
    echo $_POST["frequency"];
}
?>
</label>
</ul>
</div>

<div class="row">
<ul>
Channel Width (MHz):&nbsp;&nbsp;
<label>
<?php
if(isset ($_POST["width"]))
{
    echo $_POST["width"];
}
?>
</label>
</ul>
</div>

<div class="row">
<ul>
Total Secondary Base Stations Placed:&nbsp;&nbsp;
<label>
<?php

if (isset($_POST["choice"]))
{
    shell_exec("find ./csv -not -name 'Thane_Original.csv' -name '*.csv' -type f -delete");
    shell_exec("find ./result -name '*.json' -type f -delete");
    shell_exec("rm -rf /var/lib/mysql-files/*.csv");
    shell_exec("cp csv/Thane_Original.csv csv/place_pop_lat_long_alt_bw.csv");

    shell_exec("python calculate_coverage_radius.py $_POST[propagation_model] $_POST[transmit_power] $_POST[rx_sensitivity] $_POST[frequency]");

    $servername = "localhost";
    $username = "root";
    $password = "VAMBUJAM123";
    $dbname = "Test";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error)
    {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "truncate input_data";
    $conn->query($sql);

    shell_exec("cp ./csv/place_pop_lat_long_alt_bw.csv /var/lib/mysql-files/place_pop_lat_long_alt_bw.csv");

    $sql = sprintf("LOAD DATA INFILE '/var/lib/mysql-files/place_pop_lat_long_alt_bw.csv' INTO TABLE input_data FIELDS TERMINATED BY ','  ENCLOSED BY '\"' LINES TERMINATED BY '\n'");
    $conn->query($sql);

    $sql = "truncate coverage_data";
    $conn->query($sql);

    shell_exec("cp ./csv/place_pop_lat_long_alt_bw_range.csv /var/lib/mysql-files/place_pop_lat_long_alt_bw_range.csv");

    $sql = sprintf("LOAD DATA INFILE '/var/lib/mysql-files/place_pop_lat_long_alt_bw_range.csv' INTO TABLE coverage_data FIELDS TERMINATED BY ','  ENCLOSED BY '\"' LINES TERMINATED BY '\n'");
    $conn->query($sql);

    $sql = "drop table temp_coverage_table1";
    $conn->query($sql);

    $sql = "create table temp_coverage_table1 SELECT Sec_Tx, Sec_Tx_Lat, Sec_Tx_Long, Sec_Tx_Alt, Coverage_Radius_kms, Input_Place AS Sec_Rx, Input_Population AS Sec_Rx_Population, Input_BW_Reqt_Per_User_Mbps AS BW_Reqt_Sec_Rx, round(
                6371 * acos (
                cos ( radians(Sec_Tx_Lat) )
                * cos( radians(Input_Latitude) )
                * cos( radians(Input_Longitude) - radians(Sec_Tx_Long) )
                + sin ( radians(Sec_Tx_Lat) )
                * sin( radians(Input_Latitude) )
                ), 2)
        AS Distance
        FROM `Test`.`coverage_data`, `Test`.`input_data`
        WHERE (
                6371 * acos (
                cos ( radians(Sec_Tx_Lat) )
                * cos( radians(Input_Latitude) )
                * cos( radians(Input_Longitude) - radians(Sec_Tx_Long) )
                + sin ( radians(Sec_Tx_Lat) )
                * sin( radians(Input_Latitude) )
                )
                ) < Coverage_Radius_kms
        ORDER BY Coverage_Radius_kms DESC, Distance";
    $conn->query($sql);

    $sql = "drop table temp_coverage_table2";
    $conn->query($sql);

    $sql = "create table temp_coverage_table2 select Sec_Tx, sum(Sec_Rx_Population) AS Aggregated_Population_Within_Radius, Distance div 1 as Distance_Starting_From_kms from temp_coverage_table1 group by Distance div 1, Sec_Tx, Coverage_Radius_kms ORDER BY Coverage_Radius_kms DESC, Distance_Starting_From_kms";
    $conn->query($sql);

    $sql = "select * from temp_coverage_table2 INTO OUTFILE '/var/lib/mysql-files/input_places_population_within_radius.csv' FIELDS TERMINATED BY ',' LINES TERMINATED BY '\n'";
    $conn->query($sql);

    shell_exec("cp /var/lib/mysql-files/input_places_population_within_radius.csv ./csv/input_places_population_within_radius.csv");

    chdir("./csv/input_places_propagation_model_for_sec_base_stations/");
    shell_exec("sudo chmod a+x input_places_propagation_model_for_sec_base_stations_script.sh");
    shell_exec("./input_places_propagation_model_for_sec_base_stations_script.sh");
    chdir("../../");

    chdir("./csv/input_places_population_within_radius/");
    shell_exec("sudo chmod a+x input_places_population_within_radius_script.sh");
    shell_exec("./input_places_population_within_radius_script.sh");
    chdir("../../");

    shell_exec("python propagation_model_input_places.py $_POST[propagation_model] $_POST[transmit_power] $_POST[width] $_POST[frequency]");

    shell_exec("cat ./csv/input_places_propagation_model_for_sec_base_stations_wu_wat/*.csv > ./csv/place_pop_lat_long_alt_bw_range_wu_wat.csv");

    shell_exec("sort -t, -nrk8,8 -nrk7,7 ./csv/place_pop_lat_long_alt_bw_range_wu_wat.csv -o ./csv/place_pop_lat_long_alt_bw_range_wu_wat.csv");

    shell_exec("cp ./csv/place_pop_lat_long_alt_bw_range_wu_wat.csv /var/lib/mysql-files/place_pop_lat_long_alt_bw_range_wu_wat.csv");

    $sql = "truncate uval_data";
    $conn->query($sql);

    $sql = sprintf("LOAD DATA INFILE '/var/lib/mysql-files/place_pop_lat_long_alt_bw_range_wu_wat.csv' INTO TABLE uval_data FIELDS TERMINATED BY ','  ENCLOSED BY '\"' LINES TERMINATED BY '\n'");
    $conn->query($sql);

    $sql = "drop table temp_table1";
    $conn->query($sql);

    if ($_POST["radio_choice"] == "Coverage_Area")
    {
        $sql = "create table temp_table1 SELECT Place AS Sec_Tx, Population AS Sec_Tx_Population, Latitude AS Sec_Tx_Lat, Longitude AS Sec_Tx_Long, Altitude AS Sec_Tx_Alt, Coverage_Radius_kms, Input_Place AS Sec_Rx, Input_Population AS Sec_Rx_Population, Input_BW_Reqt_Per_User_Mbps AS BW_Reqt_Sec_Rx, round(
                6371 * acos (
                cos ( radians(Latitude) )
                * cos( radians(Input_Latitude) )
                * cos( radians(Input_Longitude) - radians(Longitude) )
                + sin ( radians(Latitude) )
                * sin( radians(Input_Latitude) )
                ), 2)
        AS Distance, Weighted_UVal AS UVal_Sec_Tx
        FROM `Test`.`uval_data`, `Test`.`input_data`
        WHERE (
                6371 * acos (
                cos ( radians(Latitude) )
                * cos( radians(Input_Latitude) )
                * cos( radians(Input_Longitude) - radians(Longitude) )
                + sin ( radians(Latitude) )
                * sin( radians(Input_Latitude) )
                )
                ) < Coverage_Radius_kms
        ORDER BY Coverage_Radius_kms DESC, UVal_Sec_Tx * 1 DESC, Sec_Tx_Population DESC, Distance";
    }

    elseif ($_POST["radio_choice"] == "Population")
    {
        $sql = "create table temp_table1 SELECT Place AS Sec_Tx, Population AS Sec_Tx_Population, Latitude AS Sec_Tx_Lat, Longitude AS Sec_Tx_Long, Altitude AS Sec_Tx_Alt, Coverage_Radius_kms, Input_Place AS Sec_Rx, Input_Population AS Sec_Rx_Population, Input_BW_Reqt_Per_User_Mbps AS BW_Reqt_Sec_Rx, round(
                6371 * acos (
                cos ( radians(Latitude) )
                * cos( radians(Input_Latitude) )
                * cos( radians(Input_Longitude) - radians(Longitude) )
                + sin ( radians(Latitude) )
                * sin( radians(Input_Latitude) )
                ), 2)
        AS Distance, Weighted_UVal AS UVal_Sec_Tx
        FROM `Test`.`uval_data`, `Test`.`input_data`
        WHERE (
                6371 * acos (
                cos ( radians(Latitude) )
                * cos( radians(Input_Latitude) )
                * cos( radians(Input_Longitude) - radians(Longitude) )
                + sin ( radians(Latitude) )
                * sin( radians(Input_Latitude) )
                )
                ) < Coverage_Radius_kms
        ORDER BY Sec_Tx_Population DESC, UVal_Sec_Tx * 1 DESC, Coverage_Radius_kms DESC, Distance";
    }

    elseif ($_POST["radio_choice"] == "Weighted_Utility")
    {
        $sql = "create table temp_table1 SELECT Place AS Sec_Tx, Population AS Sec_Tx_Population, Latitude AS Sec_Tx_Lat, Longitude AS Sec_Tx_Long, Altitude AS Sec_Tx_Alt, Coverage_Radius_kms, Input_Place AS Sec_Rx, Input_Population AS Sec_Rx_Population, Input_BW_Reqt_Per_User_Mbps AS BW_Reqt_Sec_Rx, round(
                6371 * acos (
                cos ( radians(Latitude) )
                * cos( radians(Input_Latitude) )
                * cos( radians(Input_Longitude) - radians(Longitude) )
                + sin ( radians(Latitude) )
                * sin( radians(Input_Latitude) )
                ), 2)
        AS Distance, Weighted_UVal AS UVal_Sec_Tx
        FROM `Test`.`uval_data`, `Test`.`input_data`
        WHERE (
                6371 * acos (
                cos ( radians(Latitude) )
                * cos( radians(Input_Latitude) )
                * cos( radians(Input_Longitude) - radians(Longitude) )
                + sin ( radians(Latitude) )
                * sin( radians(Input_Latitude) )
                )
                ) < Coverage_Radius_kms
        ORDER BY UVal_Sec_Tx * 1 DESC, Coverage_Radius_kms DESC, Sec_Tx_Population DESC, Distance";
    }

    $conn->query($sql);

    $sql = "select Sec_Tx, Sec_Tx_Population, Sec_Tx_Lat, Sec_Tx_Long, Sec_Tx_Alt, Coverage_Radius_kms, Sec_Rx, Sec_Rx_Population, BW_Reqt_Sec_Rx, Distance, UVal_Sec_Tx from temp_table1 INTO OUTFILE '/var/lib/mysql-files/select_sec_tx.csv' FIELDS TERMINATED BY ',' LINES TERMINATED BY '\n'";
    $conn->query($sql);

    shell_exec("cp /var/lib/mysql-files/select_sec_tx.csv ./csv/select_sec_tx.csv");

    shell_exec("python select_sec_tx.py");

    $sql = "truncate temp_table2";
    $conn->query($sql);

    shell_exec("cp ./csv/sec_tx_locations.csv /var/lib/mysql-files/sec_tx_locations.csv");

    $sql = sprintf("LOAD DATA INFILE '/var/lib/mysql-files/sec_tx_locations.csv' INTO TABLE temp_table2 FIELDS TERMINATED BY ','  ENCLOSED BY '\"' LINES TERMINATED BY '\n'");
    $conn->query($sql);

    $sql = "select Sec_Tx, sum(Sec_Rx_Population) AS Aggregate_Population, Sec_Tx_Lat, Sec_Tx_Long, Sec_Tx_Alt, max(BW_Reqt_Sec_Rx) AS BW_Reqt, Coverage_Radius_kms from temp_table2 group by Sec_Tx, Sec_Tx_Lat, Sec_Tx_Long, Sec_Tx_Alt, Coverage_Radius_kms INTO OUTFILE '/var/lib/mysql-files/propagation_model_for_sec_base_stations_input.csv' FIELDS TERMINATED BY ',' LINES TERMINATED BY '\n'";
    $conn->query($sql);

    shell_exec("cp /var/lib/mysql-files/propagation_model_for_sec_base_stations_input.csv ./csv/propagation_model_for_sec_base_stations_input.csv");

    $sql = "select Sec_Tx, sum(Sec_Rx_Population) AS Aggregated_Population_Within_Radius, Distance div 1 as Distance_Starting_From_kms from temp_table2 group by Distance div 1, Sec_Tx INTO OUTFILE '/var/lib/mysql-files/sec_tx_population_within_radius.csv' FIELDS TERMINATED BY ',' LINES TERMINATED BY '\n'";
    $conn->query($sql);

    shell_exec("cp /var/lib/mysql-files/sec_tx_population_within_radius.csv ./csv/sec_tx_population_within_radius.csv");

    chdir("./csv/sec_tx_propagation_model_for_sec_base_stations/");
    shell_exec("sudo chmod a+x sec_tx_propagation_model_for_sec_base_stations_script.sh");
    shell_exec("./sec_tx_propagation_model_for_sec_base_stations_script.sh");
    chdir("../../");

    chdir("./csv/sec_tx_population_within_radius/");
    shell_exec("sudo chmod a+x sec_tx_population_within_radius_script.sh");
    shell_exec("./sec_tx_population_within_radius_script.sh");
    chdir("../../");

    shell_exec("python propagation_model_sec_tx.py $_POST[propagation_model] $_POST[transmit_power] $_POST[width] $_POST[frequency]");

    shell_exec("python parse_json.py");
    shell_exec("sort -t, -nrk2,2 ./csv/sec_tx_weighted_utility.csv -o ./csv/sec_tx_weighted_utility.csv");
    shell_exec("sort -t, -nrk2,2 ./csv/sec_tx_weighted_average_throughput.csv -o ./csv/sec_tx_weighted_average_throughput.csv");

    shell_exec("./generate_result_json.sh");
}

$message=shell_exec("cat ./csv/sec_tx_weighted_average_throughput.csv | wc -l");
print_r($message);
?>
</label>
</ul>
</div>
</div>
</div>

<div class="col-md-8">
<noscript>
<div class="alert alert-info">
    <h4>Your JavaScript is disabled</h4>
    <p>Please enable JavaScript to view the map.</p>
</div>
</noscript>

<div id="loading">
<img id="loading-image" src="../config/images/loader.gif" alt="Loading..." width="230" height="280" align="right"/>
</div>

<div id="map_canvas"></div>
<p class="pull-right"></p>
</div>
</div>
</div>


<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

function customError($errno, $errstr)
{
    echo "<b>Error:</b> [$errno] $errstr";
}

set_error_handler("customError");
?>

<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDYAs2t-jI3LW9wPRCa7PAc0lpPIng_R68"></script>
<script type="text/javascript" src="../config/js/jquery.address.js"></script>
<script type="text/javascript" src="../config/js/bootstrap.min.js"></script>
<script type="text/javascript" src="../config/js/maps_lib.js"></script>
<script type="text/javascript">

$(window).resize(function () {
    var h = $(window).height(),
    offsetTop = 105;
    $("#map_canvas").css("height", (h - offsetTop));
}).resize();


$(function() {
    var markers;
    $.getJSON("./result/result.json")
    .done(function (getData)
    {
        markers = getData;
        console.log(markers);
        var pos = markers[0];
        var mapOptions = {center: new google.maps.LatLng(pos.lat, pos.long), zoom: 1, mapTypeId: google.maps.MapTypeId.ROADMAP};
        var map = new google.maps.Map(document.getElementById("map_canvas"), mapOptions);
        var infoWindow = new google.maps.InfoWindow();
        var lat_lng = new Array();
        var latlngbounds = new google.maps.LatLngBounds();

        for (var i = 0; i < markers.length; i++)
        {
            var image="http://findicons.com/files/icons/977/rrze/48/wifi.png";
            var data = markers[i];
            var myLatlng = new google.maps.LatLng(data.lat, data.long);
            lat_lng.push(myLatlng);
            var marker = new google.maps.Marker({position: myLatlng, map: map, draggable: false, animation: google.maps.Animation.DROP, icon: image});
            var throughput_list = data.throughput_list;
            console.log(throughput_list);
            var circles =  new Array(throughput_list.length);

            for (var j = throughput_list.length-1; j >= 0; j--)
            {
                var infowindow1 = new google.maps.InfoWindow({});

                var inter_radius = j+1;
                var inter_radius_m = inter_radius * 500;
                var title_string = "Coverage Radius (kms): " + "<b>" + inter_radius + "</b>" + "<br>" + "Throughput/User (Mbps): " + "<b>" + throughput_list[j].toFixed(2) + "</b>";

                circles[j] = new google.maps.Circle({
                    map: map,
                    center: myLatlng,
                    radius: inter_radius_m,
                    strokeColor: "black",
                    strokeOpacity: 1/inter_radius,
                    strokeWeight: 1,
                    fillColor: "green",
                    fillOpacity: 1/inter_radius
                });

                (function (marker, title_string)
                {
                    google.maps.event.addListener(marker, "mouseover", function (e)
                    {
                        console.log(title_string);
                        infoWindow.setContent(title_string);
                        infoWindow.open(map, marker);
                    });
                    google.maps.event.addListener(marker, "mouseout", function (e)
                    {
                        infoWindow.open(null);
                    });
                })(circles[j], title_string);
            }

            var circle = new google.maps.Circle({
                    map: map,
                    center: myLatlng,
                    radius: data.coverage*500,
                    strokeColor: "black",
                    strokeOpacity: 1,
                    strokeWeight: 1,
                    fillColor: "red",
                    fillOpacity: 0.1});
            (function (marker, data)
            {
                google.maps.event.addListener(marker, "mouseover", function (e)
                {
                    var contentString = "Place: " + "<b>" + data.place + "</b>" + "<br>" + "Latitude: " + "<b>" + data.lat + "</b>" + "<br>" + "Longitude: " + "<b>" + data.long + "</b>" + "<br>" + "Altitude (m): " + "<b>" + data.alt + "</b>" + "<br>" + "Coverage Radius (kms): " + "<b>" + data.coverage + "</b>" + "<br>" + "Users Served: " + "<b>" + data.users_served + "</b>" + "<br>" + "Bandwidth Requirement (Mbps): " + "<b>" + data.bw_reqt + "</b>" + "<br>" + "Weighted Utility: " + "<b>" + data.weighted_utility + "</b>" + "<br>" + "Weighted Average Throughput (Mbps): " + "<b>" + data.weighted_average_throughput + "</b>";
                    console.log(contentString);
                    infoWindow.setContent(contentString);
                    infoWindow.open(map, marker);
                });
                google.maps.event.addListener(marker, "mouseout", function (e)
                {
                    infoWindow.open(null);
                });
            })(marker, data);
        }
        map.setZoom(10);
    });
});

$(window).load(function()
{
    $("#loading").hide();
});
</script>

</body>
</html>
