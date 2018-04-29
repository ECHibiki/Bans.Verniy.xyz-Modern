<?php
	require_once("../Class/database-connection.php");
	$database = new DatabaseConnection("../");


	if($_POST["Query"] == 1){
		$JSON_Properties = $database->getPostDetails("JSONProperties");
		foreach($JSON_Properties as $key=>$json_property){
			echo $json_property[0] . " " . $json_property[1] . "-";
		}
	}
	// else if($_POST["Query"] == 2 && isset($_POST["Page"])){
		// $bans_list = $database->getPostDetailsAllUpperLower("Bans", "BanEntryID", ($_POST["Page"])*1000);
		// foreach($bans_list as $key=>$ban){
			// echo $ban["board"] . "<>" . $ban["name"] ."<>" . $ban["trip"] ."<>" . $ban["com"] ."<>" . $ban["action"] ."<>" . $ban["length"] ."<>" . $ban["reason"] ."<>" . $ban["now"] . "<>" . $ban["filename"] ."</>";
		// }
	// }
	// else if($_POST["Query"] == 3 && isset($_POST["Page"]) && isset($_POST["Board"])){
		// $bans_list_board = $database->getPostDetailsLimit("Bans", "BanEntryID", $_POST["Page"]*1000, "Board", $_POST["Board"]);
		// foreach($bans_list_board as $key=>$ban){
			// echo $ban["board"] . "<>" . $ban["name"] ."<>" . $ban["trip"] ."<>" . $ban["com"] ."<>" . $ban["action"] ."<>" . $ban["length"] ."<>" . $ban["reason"] ."<>" . $ban["now"] . "<>" . $ban["filename"] . "</>";
		// }
	// }
	else if($_POST["Query"] == 4 && isset($_POST["Board"]) && isset($_POST["Comment"]) && isset($_POST["Rule"])){
		$_POST["Board"] = trim($_POST["Board"]);
		$item_count = $database->getCountOfAllSettings("Bans", $_POST["Board"], $_POST["Comment"], $_POST["Rule"]);	
		echo ($item_count[0][0]);	
	}
	else if($_POST["Query"] == 5  && isset($_POST["Page"]) && isset($_POST["Board"]) && isset($_POST["Comment"]) && isset($_POST["Rule"])) {
		$bans_list_board = $database->getPostDetailsAllSettingsLimit("Bans", "BanEntryID", $_POST["Page"]*1000, $_POST["Board"], $_POST["Comment"], $_POST["Rule"]);
		foreach($bans_list_board as $key=>$ban){
			echo $ban["board"] . "<>" . $ban["name"] ."<>" . $ban["trip"] ."<>" . $ban["com"] ."<>" . $ban["action"] ."<>" . $ban["length"] ."<>" . $ban["reason"] ."<>" . $ban["now"] . "<>" . $ban["filename"] . "</>";
		}
		
	}
	
	//SELECT * FROM `Bans` WHERE `board` LIKE '%' AND `com` LIKE '%/qa/%' AND `reason` LIKE '%'  ORDER BY `BanEntryID` ASC LIMIT 0, 1000

?>