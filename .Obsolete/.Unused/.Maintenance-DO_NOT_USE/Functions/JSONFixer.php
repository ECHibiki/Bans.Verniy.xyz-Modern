<?php
//add [ to start
//if at the 1000th line add a ]
//put a ',' after all entries except 1000th
	function fixErrors(&$ready_file){
		$altered_data = Array();
		$line_count = 0;
		while(!feof($ready_file)){
			$changed_line = fgets($ready_file);
			if($changed_line == "") continue;
			if($line_count == 0){
				$changed_line = "[" . substr($changed_line,0, strlen($changed_line) - 1) . ",\n";
			}
			else if($line_count == 999){
				$changed_line = $changed_line . "]";
			}
			else{
				$changed_line = substr($changed_line,0, strlen($changed_line) - 1) . ",\n";
			}
			$line_count++;
			array_push($altered_data, $changed_line );
		}
		fseek($ready_file,0);
		foreach($altered_data as $line){
			fwrite($ready_file, $line);
		}
		fseek($ready_file,0);
	}
?>