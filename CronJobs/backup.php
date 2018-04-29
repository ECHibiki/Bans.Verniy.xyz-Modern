<?php 
//Pushes files to github via bot. 
//stores at https://api.github.com/repos/ECHibiki/Backup_bans.verniy.xyz 
$start_time = microtime(true) * 1000;
$github_settings = file_get_contents("../Settings/github.ini");
$token = substr($github_settings , strpos($github_settings, "=") + 1);

	include("Functions/RepoFunctions.php");
		
	//read status file
	$log_contents = json_decode(file_get_contents(__DIR__ . "/FileLog.json"), true);
	var_dump($log_contents);
	echo("<br>.........<br>");
	//acquire files from root up.
	$directory_hunt = new RecursiveDirectoryIterator("../", RecursiveDirectoryIterator::SKIP_DOTS);
	$iterator = new RecursiveIteratorIterator($directory_hunt);

	$rollback_files = Array();
	foreach($iterator as $file_name){

		//set url to send to
		$file_name = substr($file_name, 3);
		echo "$file_name<br>";
	
		$url = "https://api.github.com/repos/ECHibiki/Backup_bans.verniy.xyz/contents/$file_name";
		$curl = curl_init($url);
		
		//Authenitcate
		authenticate("https://api.github.com/user", "verniy-bot", $token, $curl);
		
		//Check for appropriate action
		$status = fileStatus($file_name, $log_contents);
		//is not in log
		echo($status[0] . " " . $status[1] . " :: ");
				
		if ($status[0] == 0) {
			createFile($curl, $file_name, $status[1], $log_contents);		
			echo"Create - $url ";
		}
		//in log, needs update 		
		else if($status[0] == 1) {
			if($file_name == NULL || strpos($file_name,".ini") !== false /*|| strpos($file_name,"RepoFunctions.php") !== false*/){
				$log_contents["Seen"][$status[1]] = 1;	
				echo"Ignored - $url <br/><br/>";
				continue;
			}
			updateFile($curl, $file_name, $status[1], $log_contents);
			echo"Update - $url ";
		}	
		//in log, no update
		else if($status[0] == -1){
			echo"Continue - $url <br/><br/>";
			
			$log_contents["Seen"][$status[1]] = 1;	
				
			continue;
		}
		else{
			echo"Unspecified Error<br/><br/>";			
			$log_contents["Seen"][$status[1]] = 1;	
			continue;
		}
		$response_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$response = curl_exec($curl);
		curl_close($curl);
		
		$sha = json_decode(substr($response, strpos($response, "{\"content\":")), true)["content"]["sha"];
		
		$log_contents["File-Name"][$status[1]]  = $file_name;	
		$log_contents["Recorded-Last-Update"][$status[1]] =  time();
		$log_contents["Sha"][$status[1]] = $sha;
		$log_contents["Seen"][$status[1]] = 1;	
				
		$code = substr($response, 8, 38);
		if(strpos($code, "4") === false){
			echo " == " . substr($response, 8, 38) . "<br/><br/>"; 
			array_push($rollback_files, $file_name);
		}
		else{
			$fail = true;
			if(strpos($code, "422") !== false || strpos($code, "409") !== false){
				$file_loc = "https://api.github.com/repos/ECHibiki/Backup_bans.verniy.xyz/contents/$file_name";
				echo $file_loc;
				$ch = curl_init($file_loc);
				authenticate("https://api.github.com/user", "verniy-bot", $token, $ch);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch,CURLOPT_USERAGENT, "Banlog-updater");
				$result = curl_exec($ch);
				$result = substr($result, strpos($result, '{'));
				echo"+=+=+=";
				echo($result);
				$sha = json_decode($result, true)["sha"];
				
				echo "||||||$file_name - $code RETRY::::Sha $sha||||||||";
				$url = "https://api.github.com/repos/ECHibiki/Backup_bans.verniy.xyz/contents/$file_name";
				$curl = curl_init($url);
				
				//Authenitcate
				authenticate("https://api.github.com/user", "verniy-bot", $token, $curl);		
				//update on retry
				updateFileDirect($curl, $file_name, $status[1], $sha);
											
				$response_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
				$response = curl_exec($curl);
				curl_close($curl);
				
				$sha = json_decode(substr($response, strpos($response, "{\"content\":")), true)["content"]["sha"];
				
				$log_contents["File-Name"][$status[1]]  = $file_name;	
				$log_contents["Recorded-Last-Update"][$status[1]] =  time();
				$log_contents["Sha"][$status[1]] = $sha;
				$log_contents["Seen"][$status[1]] = 1;	
						
				$code = substr($response, 8, 38);
				if(strpos($code, "4") === false){
					echo " == " . substr($response, 8, 38) . "<br/><br/>"; 
					$fail = false;
				}
				else $fail = true;
			}
			if($fail){
				if(strpos($code, "409") !== false) echo "///Too Large To Fetch?";
				else if(strpos($code, "400") !== false) echo "///Filename error";
				echo "<br><br>" . $response . "<br/><br/>"; 
				unset($log_contents["File-Name"][$status[1]]);	
				unset($log_contents["Recorded-Last-Update"][$status[1]]);	
				unset($log_contents["Sha"][$status[1]]);	
				unset($log_contents["Seen"][$status[1]]);	
			}
		}
	}
	
		var_dump($log_contents);
				echo  "<br>";	
		$index_counter = 0;
		
		foreach($log_contents["Seen"] as &$seen){
			if($seen == 1)
				$seen = 0;
			else{ 
				$file_name = $log_contents["File-Name"][$index_counter];
							
				echo "<br><br>Deleting $file_name";
				
				$url = "https://api.github.com/repos/ECHibiki/Backup_bans.verniy.xyz/contents/" . $file_name;
				$curl = curl_init($url);		
				//Authenitcate
				authenticate("https://api.github.com/user", "verniy-bot", $token, $curl);
				//attempt to remove file if it exists
				deleteFile($curl, $file_name, $index_counter, $log_contents["Sha"][$index_counter]);
				$data =  curl_exec($curl);
				
				// //then remove from log
				unset($log_contents["File-Name"][$index_counter]);	
				unset($log_contents["Recorded-Last-Update"][$index_counter]);	
				unset($log_contents["Sha"][$index_counter]);	
				unset($log_contents["Seen"][$index_counter]);	
				
				echo "==" .  substr($data, 8, 38);
			}
			$index_counter++;
		}
		echo "<br>" ;
		var_dump($log_contents);
		
		$log_contents["File-Name"]  = array_values($log_contents["File-Name"]);	
		$log_contents["Recorded-Last-Update"] = array_values($log_contents["Recorded-Last-Update"]);
		$log_contents["Sha"] =  array_values($log_contents["Sha"]);
		$log_contents["Seen"] = array_values($log_contents["Seen"]);
		
		$log = fopen(__DIR__ . "/FileLog.json", "w");
		fwrite($log, json_encode($log_contents));
		fclose($log);
		
echo "<br/><br/>" . (microtime(true) * 1000 - $start_time);
?>