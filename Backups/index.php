<html>
<head>
<base href="/Backups/" target="_blank">
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
		foreach($files as $index=>$file){
				$sorted_files[$index] = $file;
		}
		echo "<h1 itemprop='name'>Backups</h1>";
		
		echo "<div id='container'>
		<p itemprop='description'>The following are all the SQL Database Updates.</p>
		<ol>";
		for ($i = 0 ; $i < sizeof($sorted_files); $i++){
			if($sorted_files[$i] != "index.php" && $sorted_files[$i][0] != "."){
				echo "<li><strong><a href ='" . ($sorted_files[$i]) . "' download='" . ($sorted_files[$i]) . "'>" . $sorted_files[$i] . "</a></strong></li>";
			}
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