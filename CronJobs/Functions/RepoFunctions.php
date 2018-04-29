<?php 
//ecverniy.bot69@  "verniy-bot:"
function authenticate($url, $user, $pass, &$curl){
	curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	curl_setopt($curl,CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($curl,CURLOPT_USERAGENT, "Banlog-updater");
	curl_setopt($curl, CURLOPT_HEADER, true);
	curl_setopt($curl, CURLOPT_USERPWD, "$user:$pass");
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);//set to on
}

// path	string	Required. The content path.
// message	string	Required. The commit message.
// content	string	Required. The new file content, Base64 encoded.
//https://stackoverflow.com/questions/36835116/how-to-create-and-update-a-file-in-a-github-repository-with-php-and-github-api
function createFile(&$curl, $file_name, &$log_position, &$log_contents){

	if($file_name == NULL || strpos($file_name,".ini") !== false /*|| strpos($file_name,"RepoFunctions.php") !== false*/){
		echo " /////// Auto Init $file_name ////";
		$initialization_message = base64_encode("Automatic-File Initialization/Files obscured");
	}
	else{
		$initialization_message = base64_encode(file_get_contents("../" . $file_name));		
	}

	$data = 
		"{
	  \"message\": \"Initialization of $file_name\",
	  \"committer\": {
		\"name\": \"Verniy-Bot\",
		\"email\": \"ecverniy.bot69@gmail.com\"
	  },
	  \"content\": \"$initialization_message\"
	}";


	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
	curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	
		
	$log_position = sizeof($log_contents["File-Name"]);
}
//https://stackoverflow.com/questions/19888832/github-api-update-a-file-in-php
function updateFile(&$curl, $file_name, &$log_position, &$log_contents){
	if($file_name == NULL /*|| strpos($file_name,"backup.php") !== false || strpos($file_name,"RepoFunctions.php") !== false*/){
		$update_message = base64_encode("Automatic-File update");
	}
	else{
		$update_message = base64_encode(file_get_contents("../" . $file_name));		
	}
	$data = 
		"{
	  \"message\": \"Update of $file_name\",
	  \"sha\": \"" . $log_contents["Sha"][$log_position] ."\",
	  \"committer\": {
		\"name\": \"Verniy-Bot\",
		\"email\": \"ecverniy.bot69@gmail.com\"
	  },
	  \"content\": \"$update_message\"
	}";

	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
	curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
}

function updateFileDirect(&$curl, $file_name, &$log_position, &$sha){
	if($file_name == NULL || strpos($file_name,".ini") !== false /*|| strpos($file_name,"RepoFunctions.php") !== false*/){
		$update_message = base64_encode("Automatic-File update/ obscured");
	}
	else{
		$update_message = base64_encode(file_get_contents("../" . $file_name));		
	}
	$data = 
		"{
	  \"message\": \"Update of $file_name\",
	  \"sha\": \"" . $sha ."\",
	  \"committer\": {
		\"name\": \"Verniy-Bot\",
		\"email\": \"ecverniy.bot69@gmail.com\"
	  },
	  \"content\": \"$update_message\"
	}";

	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
	curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
}

function deleteFile(&$curl, $file_name, &$log_position, &$sha){
	$data = 
		"{
	  \"message\": \"Deletion of $file_name\",
	  \"sha\": \"" . $sha ."\",
	  \"committer\": {
		\"name\": \"Verniy-Bot\",
		\"email\": \"ecverniy.bot69@gmail.com\"
	  },
	}";	
				
}

function fileStatus($file_name, &$log_contents){
	//1. scan for file names
	//1ai. if not found return 0
	//1aii.
	$exists = 0;
	$entry_no = 0;
	foreach($log_contents["File-Name"] as $log_file){
		if($log_file == $file_name){
			//may require no update. check for certainty
			$exists = -1;
									  // "../" removed in backup.php
			$filetime = filemtime("../" . $file_name);
			if($log_contents["Recorded-Last-Update"][$entry_no] < $filetime){
				//requires update
				$exists = 1;
			}
			break;
		}
		$entry_no++;
	}
	return array($exists, $entry_no);
}
?>