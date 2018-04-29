<?php

/*
http://php.net/manual/en/xmlreader.readstring.php
*/
function read_string($reader) { 
    $node = $reader->expand(); 
    return $node->textContent; 
} 

//files to check
$ledger_url = "../4Chan_Bans_Log-Ledger.txt";
$ledger_contents = explode("\n", fread(fopen($ledger_url, "r"), filesize($ledger_url)));

$number_of_files = intval($ledger_contents[1]);
$ledger_string = [];
$write_test = [];
while($number_of_files > 0){
	array_push($ledger_string, "http://bans.verniy.xyz/Logs/4Chan_Bans_Log-Reverse_Chrono-".$number_of_files.".json");
	array_push($ledger_string, "http://bans.verniy.xyz/pages?file=".($number_of_files + 1));
	array_push($write_test, true);
	array_push($write_test, true);
	$number_of_files--;
}

//cehcks sitemap for changes
$xml_reader = new XMLReader;
$xml_reader->open("../sitemap.xml");

while($xml_reader->read()){
	if($xml_reader->nodeType == XMLReader::ELEMENT && $xml_reader->name == "loc"){
		foreach($ledger_string as $index=>$ledger_item){
			$file_point = ceil($index / 2 + 1);
			if(strcmp($xml_reader->readString(), $ledger_string[$index]) == 0){
				//echo $xml_reader->readString(). " Found $file_point \n";
				$write_test[$index] = false;
			}
		}
	}	
}
echo "<br/><br/>";
$xml_reader->close();

//change sitemap
$dom_sitemap = new DOMDocument;
$dom_sitemap->formatOutput = true;
$dom_sitemap->load("../sitemap.xml");

$head = $dom_sitemap->getElementsByTagName("urlset")->item(0);

foreach($write_test as $index=>$write){
	if($write){
		$url = $dom_sitemap->createElement("url");
		$head->appendChild($url);

		$loc = $dom_sitemap->createElement("loc");
		$loc_text = $dom_sitemap->createTextNode($ledger_string[$index]);
		$loc->appendChild($loc_text);
		$url->appendChild($loc);

		$lastmod = $dom_sitemap->createElement("lastmod");
		$file_mod_time = "";
		if($index % 2 == 0){
			$filename = substr($ledger_string[$index], 27);
			$file_mod_time = date ("Y-m-dTH:i:s", filemtime("../Logs/$filename")) . "+00:00";
			$file_mod_time =  str_replace("CST", "T", $file_mod_time);
		}
		else{
			$filename = substr($ledger_string[$index  - 1], 27);
			$file_mod_time = date ("Y-m-dTH:i:s", filemtime("../Logs/$filename")) . "+00:00";
		}
		$lastmod_text = $dom_sitemap->createTextNode($file_mod_time);
		$lastmod->appendChild($lastmod_text);
		$url->appendChild($lastmod);

		$priority = $dom_sitemap->createElement("priority");
		
		$priority = $dom_sitemap->createElement("priority");
		$priority_text = $dom_sitemap->createTextNode("0.64");
		$priority->appendChild($priority_text);
		$url->appendChild($priority);
	}
}

//write to file;
$contents = $dom_sitemap->saveHTML();
echo("<pre>$contents");
$sitemap = fopen("../sitemap.xml", "w");
fwrite($sitemap, $contents);

?>