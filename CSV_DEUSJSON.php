<?php

$file_name = ""; //String with user input file name
$file; // Handler with CSV file
$string_date; //Array of date readed from CSV string
$data; //CSV data array;
$current_month = 0;
$calc_string = "";
$json_array = array("hash"=>"", "contents"=>array("periods"=>array()));
    $latitude = 0;
    $longitude = 0;
    $sunrise_angle = 90.56;
    $sunset_angle = 90;
    
//{"hash": "7fdd53d21d32dee85368f31aca7297e3", "contents": {"periods": [{"id": 1, "days": [{"id": 1, "on": "17:08", "off": "09:22"}, {"id": 2, "on": "17:10", "off": "09:21"}, {"id": 3, "on": "17:12", "off": "09:20"}, {"id": 4, "on": "17:14", "off": "09:19"}, {"id": 5, "on": "17:16", "off": "09:18"}, {"id": 6, "on": "17:18", "off": "09:17"}, {"id": 7, "on": "17:20", "off": "09:16"}, {"id": 8, "on": "17:22", "off": "09:15"}, {"id": 9, "on": "17:24", "off": "09:14"},
if (!isset($argv[1])){
	echo "CSV to DEUS_JSON converter\n";
	echo "Enter CSV filename: ";
	$file_name = trim(fgets(STDIN));
}else{
	$file_name = trim($argv[1]);
}
    /*
    echo "Set timezone: ";
    $time_zone = trim(fgets(STDIN));
    echo "Set GPS LAT: ";
    $latitude = floatval(trim(fgets(STDIN)));
    echo "Set GPS LON: ";
    $longitude = floatval(trim(fgets(STDIN)));
    echo "Set visible sunrise: (standard civil view 90.56): ";
    $sunrise_angle = floatval(trim(fgets(STDIN)));
    echo "Set visible sunset: (standard civil view 90): ";
    $sunset_angle = floatval(trim(fgets(STDIN)));
    */
    $time_zone = 3;
    $longitude = 56.37595;
    $latitude = 47.53071;
    $sunrise_angle = 90.50;
    $sunset_angle = 90.50;
    
if (file_exists($file_name.".csv")){
	$file = fopen($file_name.".csv", "r");
	if ($file !== false){
	fgetcsv($file, 1000, ";"); // Read first string, it's caption of standart CSV file
	while (($data = fgetcsv($file, 1000, ";")) !== FALSE){
	$string_date = explode(".", $data[0]);
		if ($data[0] != ""){
			if ($current_month != $string_date[1]){
				$current_month = intval($string_date[1]);
				echo "Month: " . $current_month . "\n";
				$calc_string .= intval($current_month);
				array_push($json_array["contents"]["periods"], array("id"=>intval($current_month), "days"=>array()));
			}
            //echo "Date:" . $string_date[2].".".$string_date[1].".".$string_date[0]."\n";
            $calc_time = mktime(12,0,0,$string_date[1],$string_date[0],$string_date[2]);
            $output_string = date("d M y", $calc_time);
            print "Unix time $calc_time ($output_string)\n";
			echo "Day: " . $string_date[0] . " On time: " . $data[1] . " Off time: " . $data[2] . "\n";
            $temp = explode(":", $data[1]);
            $sunset = mktime($temp[0], $temp[1], 0, $string_date[1], $string_date[0], $string_date[2]);
            $temp  = explode(":", $data[2]);
            $sunrise = mktime($temp[0], $temp[1], 0, $string_date[1], $string_date[0], $string_date[2]);
            $sunrise_calc = date_sunrise($calc_time, SUNFUNCS_RET_TIMESTAMP, $latitude, $longitude, $sunrise_angle, 0) + ($time_zone * 3600);
            $sunset_calc = date_sunset($calc_time, SUNFUNCS_RET_TIMESTAMP, $latitude, $longitude, $sunset_angle, 0) + ($time_zone * 3600);
            $sunrise_delta = $sunrise_calc - $sunrise;
            $sunset_delta = $sunset_calc - $sunset;
            
            echo "Schedule time ON: " . date("G:i", $sunset) . " | Calculated time ON: " . date("G:i", $sunset_calc) . " [DIFF: " . $sunset_delta . "]\n";
            echo "Schedule time OFF: " . date("G:i", $sunrise) . " | Calculated time OFF: " . date("G:i", $sunrise_calc) . " [DIFF: " . $sunrise_delta . "]\n";
            
			$calc_string .= intval($string_date[0]) . $data[1] . $data[2];
			array_push($json_array["contents"]["periods"][$current_month-1]["days"],array("id"=>intval($string_date[0]), "on"=>$data[1], "off"=>$data[2]));
		}
	}
	fclose($file);
	$file = fopen($file_name.".json","w");
	//echo $calc_string . "\n";
	//echo md5($calc_string) . "\n";
	$json_array["hash"] = md5($calc_string);
	//print_r($json_array);
	//echo json_encode($json_array);
	fputs($file, json_encode($json_array));
	fclose($file);
	echo "Done\n";
	}else{
		echo "File read error\n";
	}
}else{
	echo "File not found\nExit\n";
}
?>
