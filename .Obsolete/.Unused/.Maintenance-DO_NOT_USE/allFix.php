<?php
//perform all maintenance
	include("Functions/JSONFixer.php");
	include("Functions/ExtensionFixer.php");

	$log_dir = scandir("../../Logs");
	foreach ($log_dir as $file){
		if(strpos($file, "4Chan_Bans_Log-Reverse_Chrono-") !== false){
			$file_handle;
			$file_path = "../../Logs/$file";
			$file_handle = fopen($file_path, "r+");
			
			//by reference rewrites file
			fixErrors($file_handle);
			changeExtension($file_handle, $file_path);
			
			fclose($file_handle );
			echo "Done $file -> $file_path <br/>";
		}
	}
?>