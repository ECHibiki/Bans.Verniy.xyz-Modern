<?php
	require_once("Class/database-construction.php");
	$database = new DatabaseConstruction();
	$file_count = explode("\n", file_get_contents("4Chan_Bans_Log-Ledger.txt"))[1];
	echo "<pre>";
	for($file = 0 ; $file < $file_count; $file++){
		echo "$file";
		$file_data = explode("\n", file_get_contents("Logs/4Chan_Bans_Log-Reverse_Chrono-$file.json"));
		$number_of_lines = sizeof($file_data);
		foreach($file_data as $index=>$file_line){
			if(trim($file_line) == "" || trim($file_line) == "\n")
				continue;
			
			if($index == 0) $file_line = JSON_Decode(substr($file_line, 1, mb_strlen($file_line) - 2), true);
			else if($index == (sizeof($file_data) - 1)) $file_line = JSON_Decode(substr($file_line, 0, mb_strlen($file_line) - 1), true);
			else $file_line = JSON_Decode(substr($file_line, 0, mb_strlen($file_line) - 1), true);

			$database->addToTable("Bans", ["board"=> $file_line["board"], "now"=> $file_line["now"], 
											"name"=> $file_line["name"], "trip"=> $file_line["trip"],
											"com"=> $file_line["com"], "time"=> $file_line["time"], 
											"md5"=> $file_line["md5"], "filename"=> $file_line["filename"], 
											"action"=> $file_line["action"], "length"=> $file_line["length"],
											"reason"=> $file_line["reason"]]);
			
			echo ".";
		}
		echo "<hr/>";
	}
	

	//final file special handeling
	echo "$file_count";
	$file_final = explode("\n", file_get_contents("Logs/4Chan_Bans_Log-Reverse_Chrono-$file_count.json"));
	$number_of_lines = sizeof($file_final);
	foreach($file_final as $index=>$file_line){
		if(trim($file_line) == "" || trim($file_line) == "\n")
			continue;
		
		if($index == 0) $file_line = JSON_Decode(substr($file_line, 1, mb_strlen($file_line) - 2), true);
		else $file_line = JSON_Decode(substr($file_line, 0, mb_strlen($file_line) - 1), true);

		$database->addToTable("Bans", ["board"=> $file_line["board"], "now"=> $file_line["now"], 
										"name"=> $file_line["name"], "trip"=> $file_line["trip"],
										"com"=> $file_line["com"], "time"=> $file_line["time"], 
										"md5"=> $file_line["md5"], "filename"=> $file_line["filename"], 
										"action"=> $file_line["action"], "length"=> $file_line["length"],
										"reason"=> $file_line["reason"]]);
		
		echo ".";
	}


?>