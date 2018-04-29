<?php
	require_once("../Class/database-construction.php");
	$database = new DatabaseConstruction("../");
	$top_file = explode("\n", file_get_contents("../4Chan_Bans_Log-Ledger.txt"))[1];
	
	$database_add_array = [];
	$database_update_array = [];
	while($top_file >= 0){
		$current_json_file = explode("\n", file_get_contents("../Logs/4Chan_Bans_Log-Reverse_Chrono-$top_file.json"));
		echo "<pre>";
		$newest_entry_time = "" . JSON_Decode(substr($current_json_file[count($current_json_file) - 2], 0, -1), true)["time"];
		array_push($database_add_array, ["FileNumber"=>$top_file, "NewestTime"=>$newest_entry_time]);
		$top_file--;
	}
	//uses duplicate checking
	$database->massAddToTable("JSONProperties", $database_add_array);
?>