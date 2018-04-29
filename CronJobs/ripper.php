<?php
//Retrieve data from URL
function curl_get_contents($url)
{
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 5.1; rv:12.0) Gecko/20100101 Firefox/12.0');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  $data = curl_exec($ch);
  curl_close($ch);
  
  //echo($data);
  
  return $data;
}
//make into html doc
function file_get_html($url)
{
	/*disable error*/
	libxml_use_internal_errors(true);
	$doc = new DOMDocument();
	$doc->loadHTML(curl_get_contents($url));
	return $doc;
}


////////////////

//Site to be ripped
$dom = file_get_html("https://www.4chan.org/bans");
//Data to be ripped
$table = $dom->getElementById('log-entries');
//Table lookup
$i = 0;
$j = 0;
$rowContents = array();
$rowDivisions = array(); 

//get JSON
$json = substr(
				str_replace("var postPreviews = ", "", $dom->getElementsByTagName('script')->item(1)->nodeValue)
				, 0, -4);
$json = json_decode($json, true);
foreach($table->getElementsByTagName('td') as  $key =>$td){
	//on hover links get data
	if($i % 6  == 3){
		//data-pid is an attribute attatched to links on the 4chan ban page to direct them to the JSON representation of bans from the script tag
		$jsonID = $td->firstChild->getAttribute('data-pid');	
		echo $jsonID . "<br/>";
		$json[$jsonID]["action"] = $td->previousSibling->previousSibling->previousSibling->previousSibling->nodeValue;
		$json[$jsonID]["length"] = $td->previousSibling->previousSibling->nodeValue;
		$json[$jsonID]["reason"] = $td->nextSibling->nextSibling->nodeValue;
		$rowContents[$j++]  = $json[$jsonID];
	}
	$i++;
}

echo("<br>");echo("<br>---------============-------------");echo("<br>");echo("<br>");
		
		
//ledger data retrieve
$lines = array();
$leger_data = fopen("/home4/ecorvid/bans.verniy.xyz/4Chan_Bans_Log-Ledger.txt", "r");
$all_ledger_data = array();
$ledger_line = 0;
while(!feof($leger_data)){
	$all_ledger_data[$ledger_line++] = fgets($leger_data);
}
fclose($leger_data);
//ledger holds total entries in database followed by the page one filename
$total_entries = trim($all_ledger_data[0]);
$page_one_file = trim($all_ledger_data[1]);

//get current file data
$logStore = fopen("/home4/ecorvid/bans.verniy.xyz/Logs/4Chan_Bans_Log-Reverse_Chrono-$page_one_file.json", "r") or die ("could not read 4Chan_Bans_Log-Reverse_Chrono-$page_one_file");
$log_lines = 0;
while (!feof($logStore)) {
	$line = fgets($logStore);
	if($line == "") continue;
	if($log_lines == 0) {
		$line = substr($line,1);
		$line[strpos($line, ",", strlen($line) - 3)] = "";
	}
	else if($log_lines == 999)$line[strpos($line, "]", strlen($line) - 3)] = "";
	else $line[strpos($line, ",", strlen($line) - 3)] = ""; 
	$line = substr($line,0, strlen($line) - 2);
	
	$lines[$log_lines++] =  $line;
}
$line_count = count($lines);
fclose($logStore);
		
//Process JSON and store in current file
if($line_count <= 1000)
	$logFile = fopen("/home4/ecorvid/bans.verniy.xyz/Logs/4Chan_Bans_Log-Reverse_Chrono-$page_one_file.json", "a") or die ("could not read 4Chan_Bans_Log-Reverse_Chrono-$page_one_file");
else{
	//move into another file due to excess data
	$page_one_file++;
	$logFile = fopen("/home4/ecorvid/bans.verniy.xyz/Logs/4Chan_Bans_Log-Reverse_Chrono-$page_one_file.json", "w") or die ("could not read 4Chan_Bans_Log-Reverse_Chrono-$page_one_file");
}
$new_entries = 0;

require_once("../Class/database-construction.php");
$database = new DatabaseConstruction("../");

for($json_lines = count($rowContents) - 1 ; $json_lines >= 0  ; $json_lines--){
	//table row data
	$logLine = json_encode($rowContents[$json_lines]);
	
	$pass = true;
	foreach($lines as $key => $line){
		if(strcmp($line, $logLine) == 0){
			echo "||++++++++++++||<br>$line<br>FF++++++++++++FF<br>$logLine<br>||++++++++++++||
				<br>---------------------";
			$pass = false;
			break;
		}
	}
	if($pass){
		echo("<br><br>XXXXXXXXXXXX<br>" . $logLine . "<br>XXXXXXXXXXXX<br><br>");	
		if(($line_count + $new_entries) >= 1000){
			fclose($logFile);
			$page_one_file++;
			$logFile = fopen("/home4/ecorvid/bans.verniy.xyz/Logs/4Chan_Bans_Log-Reverse_Chrono-$page_one_file.json", "a") or die ("could not read 4Chan_Bans_Log-Reverse_Chrono-$page_one_file");
			echo $line_count . " " . $new_entries;
			$line_count = 0;
			$logLine = "[" . $logLine . ",";
		}
		else if(($line_count + $new_entries) == 999){
			$logLine = $logLine . "]";
		}
		else {
			$logLine = $logLine . ",";
		}
		$new_entries++;
		
		$database->addToTable("Bans", ["board"=> $rowContents[$json_lines]["board"], "now"=> $rowContents[$json_lines]["now"], 
											"name"=> $rowContents[$json_lines]["name"], "trip"=> $rowContents[$json_lines]["trip"],
											"com"=> $rowContents[$json_lines]["com"], "time"=> $rowContents[$json_lines]["time"], 
											"md5"=> $rowContents[$json_lines]["md5"], "filename"=> $rowContents[$json_lines]["filename"], 
											"action"=> $rowContents[$json_lines]["action"], "length"=> $rowContents[$json_lines]["length"],
											"reason"=> $rowContents[$json_lines]["reason"]]);
		fwrite($logFile, $logLine . "\n");	
	}
	echo("<br><br>");		
}
	//update ledger
	$leger_data = fopen("/home4/ecorvid/bans.verniy.xyz/4Chan_Bans_Log-Ledger.txt", "w");
	//update Total
	fwrite($leger_data, $all_ledger_data[0] + $new_entries . "\n");
	fwrite($leger_data, $page_one_file . "\n");
	fclose($logFile);
?>