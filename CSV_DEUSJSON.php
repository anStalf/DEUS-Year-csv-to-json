<?php

$file_name = ""; //String with user input file name
$file; // Handler with CSV file
$string_date; //Array of date readed from CSV string
$data; //CSV data array;
$current_month = 0;
$calc_string = "";
$json_array = array("hash"=>"", "contents"=>array("periods"=>array()));

//{"hash": "7fdd53d21d32dee85368f31aca7297e3", "contents": {"periods": [{"id": 1, "days": [{"id": 1, "on": "17:08", "off": "09:22"}, {"id": 2, "on": "17:10", "off": "09:21"}, {"id": 3, "on": "17:12", "off": "09:20"}, {"id": 4, "on": "17:14", "off": "09:19"}, {"id": 5, "on": "17:16", "off": "09:18"}, {"id": 6, "on": "17:18", "off": "09:17"}, {"id": 7, "on": "17:20", "off": "09:16"}, {"id": 8, "on": "17:22", "off": "09:15"}, {"id": 9, "on": "17:24", "off": "09:14"},
if (!isset($argv[1])){
	echo "CSV to DEUS_JSON converter\n";
	echo "Enter CSV filename: ";
	$file_name = trim(fgets(STDIN));
}else{
	$file_name = trim($argv[1]);
}
if (file_exists($file_name.".csv")){
	$file = fopen($file_name.".csv", "r");
	if ($file !== false){
	fgetcsv($file, 1000, ";"); // Read first string, it's caption of standart CSV file
	while (($data = fgetcsv($file, 1000, ";")) !== FALSE){
	$string_date = explode(".", $data[0]);
		if ($data[0] != ""){
			if ($current_month != $string_date[1]){
				$current_month = intval($string_date[1]);
				//echo "Month: " . $current_month . "\n";
				$calc_string .= intval($current_month);
				array_push($json_array["contents"]["periods"], array("id"=>intval($current_month), "days"=>array()));
			}
			//echo "Day: " . $string_date[0] . " On time: " . $data[1] . " Off time: " . $data[2] . "\n";
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