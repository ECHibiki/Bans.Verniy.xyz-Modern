<html>
<head>
<base href="/Logs/" target="_blank">
<title>Simple 4Chan Ban Log - Raw Logs</title>
<link rel="stylesheet" type="text/css" href="../Scripts/IndexStyle.css">
<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body itemscope="" itemtype="http://schema.org/DataCatalog">
<meta itemprop="description" content="">
<a href="../listings">Back to Listings</a> <a href="../index">Back to Index</a>
	<?php
		$files = scandir(__DIR__ . "/");
		$sorted_files = Array();
		foreach($files as $file){
			if(strpos($file, "4Chan") !== false){
				$sorted_files[
					substr(
						$file,
						strrpos($file, "-") + 1,
						strrpos($file, ".") - strrpos($file, "-") - 1)
					] = $file;
			}
		}
		echo "<h1 itemprop='name'>Raw Logs</h1>";
		
		echo "<div id='json_container'><h2>JSON Logs</h2>
		<p>The following logs are displayed in reverse chronological. Log 0 is therefore the oldest on record and at the bottom is the newest</p>
		<ol>";
		for ($i = 0 ; $i < sizeof($sorted_files); $i++){
			echo "<li><strong><a href ='4Chan_Bans_Log-Reverse_Chrono-" . "$i" . ".json'>" . $sorted_files[$i] . "</a></strong></li>";
		}
		echo "</ol></div>";
	?>
			<div itemprop="about" class='p-4'>
			<p>
				<span itemprop="accountablePerson">Verniy 2017-2018</span><br/>
				<meta itemprop="author" content="ECHibiki">
				Inquiries to be sent to the gmail account of ECVerniy
			</p>
		</div>
</body>
</html>