<?php

//************To be used on a mass file********//

	//read from file, put into splits, update ledger info
    $file = fopen("Backups/4Chan_Bans_Log(07-12-2017).txt", "r");
	
	//get the contents of the file into an array
	$file_contents = array();
	$file_contents_index = 0;
	while(!feof($file)) {
		$file_contents[$file_contents_index++] = fgets($file);
	}
	//remove blank
	unset($file_contents[count($file_contents) - 1]);
		
	//insert into a divided 2D array
	$file_index = 0;
	$line_counter = 0;
	$line_array = array();
	$line_array[$file_index] = array();
	foreach($file_contents as &$file_line) {
		$line_array[$file_index][$line_counter++] = $file_line;
		if($line_counter >= 1000){
			$line_array[++$file_index] = array();
			$line_counter = 0;
		}
	}
	$file_index = 0;
	$line_counter = 0;
	
	//create ledger data
	$ledger_file = fopen("4Chan_Bans_Log-Ledger.txt", "w");
	$max_files = count($line_array) - 1;
	$max_entries = count($file_contents);
	//write to files
	fwrite($ledger_file, $max_entries. "\n");
	foreach($line_array as &$index){
		$split_file = fopen("Logs/4Chan_Bans_Log-Reverse_Chrono-$file_index.txt", "w");
		foreach($index as $line){
			fwrite($split_file, $line);
		}
		fclose($split_file);
		$current_index = $max_files-$file_index;
		echo $file_index . " " .$current_index . "</br>";
		fwrite($ledger_file, $current_index . "\n");
		$file_index++;
	}
	fclose($ledger_file);
	fclose($file);
	
	echo "Done";
?>