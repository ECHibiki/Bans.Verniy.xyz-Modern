<?php
//replace .txt with .json
	function changeExtension(&$ready_file, &$file_path){
		$new_name = preg_replace("/.txt/", ".json", $file_path);
		rename($file_path, $new_name);
		fclose($ready_file);
		$file_path = $new_name;
		$ready_file = fopen("$new_name", "r");
	}
?>