<!DOCTYPE html>
<html>
	<head>
	<base href="http://bans.verniy.xyz"/>
			<!-- Latest compiled and minified CSS -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css">

		<!-- jQuery library -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

		<!-- Popper JS -->
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>

		<!-- Latest compiled JavaScript -->
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js"></script>

		<title>Simple 4Chan Ban Log - Paged View</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=0.8">
	<!--	<script type="text/javascript" src = "/Scripts/TableFunctions.js?5"></script>
	
		<!--<link href="/Scripts/IndexStyle.css" rel="stylesheet" type="text/css">-->
	</head>
	<body itemscope="" itemtype="http://schema.org/DataCatalog">
		<div>
			<nav class="navbar navbar-expand-sm bg-secondary">
			  <ul class="navbar-nav">
				<li class="nav-item">
				  <a class="nav-link text-success" href="/">Home</a>
				</li>
				<li class="nav-item">
				  <a class="nav-link text-success" href="/main">Listing</a>
				</li>
				<li class="nav-item">
				  <a class="nav-link text-success" href="http://verniy.xyz">Root</a>
				</li>
			  </ul>
			</nav>
			<meta itemprop='url' content=\"/pages?file=" . $_GET['file'] . "\"/>
<?php

// error_reporting(0);

require_once("Class/database-connection.php");

$database = new DatabaseConnection();
$entry_count = $database->getCountOfAllSettings("Bans", $_GET["ref"], $_GET["com"], $_GET["rul"])[0][0];	
$file_count = ceil($entry_count / 1000) - 1;
if($_GET["file"] >  $file_count) $_GET["file"] = $file_count + 1;
else if($_GET["file"] <= 0) $_GET["file"] = 1;


echo "
		<div class='jumbotron jumbotron-fluid p-5 m-0'>
		<h2 itemprop='name' class='display-4'>The 4Chan Ban Logger - Pages</h2>
		<meta itemprop='description' content='An easy to read log of 4chan's ban page for both the ban evader and the innocent alike. Contains information what 4chan moderators ban by archiving the 4chan ban page at 4chan.org/bans. Updated every 15 minutes to stay up to date. Common bans such as GR15, GR5 etc. from every board(/a/, /pol/, /e/, /h/)'>
			<meta itemprop='keywords' content='4chan,bans,logger,shitposter,GR3,ban evasion,/a/,/c/,/h/,/d/,/e/'>
			<meta itemprop='version' content='Jan-03' id='versionInfo'/>

<p>
<form action='/pages' method='GET'>
				
				<input id=\"rulerefine\" type=\"text\" class='form-control' placeholder='eg. Global 5' name='file' value='".$_GET["file"]."' hidden/>
				
<div id='searchcard' class='container-fluid p-3 bg-light card' style=\"opacity:1.0\" onmouseenter='' onmouseleave=''>
				<div class=\"input-group input-group-md\" style=\"display:auto\"><div class=\"input-group-prepend\"><span class=\"input-group-text bg-light\">Search for Board</span></div>
					<input id=\"refinement\" type=\"text\" class='form-control' placeholder='' name='ref' value='".$_GET["ref"]."' /></div>
				<!-- -->
				<div class=\"input-group input-group-md\"><div class=\"input-group-prepend\"><span class=\"input-group-text bg-light\">Search Comment</span></div>
					<input id=\"commentrefine\" type=\"text\" class='form-control' placeholder='' name='com' value='".$_GET["com"]."' \"/>
				</div>
				<div class=\"input-group input-group-md mb-3\"><div class=\"input-group-prepend\"><span class=\"input-group-text bg-light\">Find Specific Rule</span></div> 
					<input id=\"rulerefine\" type=\"text\" class='form-control' placeholder='eg. Global 5' name='rul' value='".$_GET["rul"]."' />
				</div>
				 <div class=\"input-group input-group-md\" style=\"display:auto\">
						<span id=\"count\" class ='input-group-text bg-light'>Total entries: $entry_count</span>	 
						<input id=\"set\" onclick=\"\" type=\"submit\" value=\"Refine\" class=''/>
						<span id=\"pages\" class='input-group-text bg-light font-italic'>Currently reading page <span class='font-weight-bold px-1 py-0'>". ($_GET["file"]) ."</span > of <span class='font-italic px-1 py-0'>" . ($file_count + 1) ."</span><br></span> 
						<input id=\"refresh\" onclick=\"\" type =\"submit\" value = \"Refresh\"  class =''></div>
			</div>

		
		
		</p>
</div></form>";


$low = ($_GET["file"] - 1);
if($low < 1) $low = 1;

$high = ($_GET["file"]) + 1;
if($high > $file_count + 1) $high = $file_count + 2;

$file_no = $file_count+1 - $_GET["file"] + 1; 

$file_contents = $database->getPostDetailsAllSettingsLimit("Bans", "BanEntryID", ($file_count - $_GET["file"] + 1 )*1000, $_GET["ref"], $_GET["com"], $_GET["rul"]);

echo "			<div class='pb-3  px-1'>
				<div class='page_table border-left d-flex flex-row flex-wrap '>";
$offset = 6;
$file_get = $_GET["file"] - $offset;
if($file_get < 0) $file_get = 0;

$font_size = "18px";
for($i = $file_get ; $i <  $file_count + 1 ; $i++){
		if($i == $_GET["file"] - 1) echo "<div style='min-width:50px;' class='text-center border rounded'><a style='font-size:$font_size;color:red' href='/pages?file=" . ($i + 1) ."'>". ($i + 1) . "</a></div>";
		else if((ceil(($file_count + 1) / 2) + $file_get) < $file_count - 2 && $i == 10 + $file_get){
			echo "<div style='min-width:50px;' class='text-center border rounded'><a style='font-size:$font_size;'
						href='/pages?file=" . (ceil(($file_count + 1) / 2) + $file_get) ."&ref=".$_GET["ref"]."&com=".$_GET["com"]."&rul=".$_GET["rul"]."'>"
						. (ceil(($file_count + 1) / 2) + $file_get) . "</a></div>";
		}
		else if($i == $file_count){
			echo "<div style='min-width:50px;' class='text-center border rounded'><a style='font-size:$font_size;' href='/pages?file=" . ($i + 1) ."&ref=".$_GET["ref"]."&com=".$_GET["com"]."&rul=".$_GET["rul"] ."'>". ($i + 1) . "</a></div>";
		}
		else if($i == 11 + $file_get || $i == 9 + $file_get){
			echo "<div style='min-width:50px;background-color:#e9ecef' class='text-center ' style='font-size:$font_size'>...</div>";
		}
		else if($i < 11 + $file_get){
			echo "<div style='min-width:50px;' class='text-center border rounded'><a style='font-size:$font_size;' href='/pages?file=" . ($i + 1)."&ref=".$_GET["ref"]."&com=".$_GET["com"]."&rul=".$_GET["rul"]."'>". ($i + 1) . "</a></div>";
		}
}
echo "</div></div></span></span></div>";

echo "<div class='p-2'><a href='/main'>View the Dynamic Listings</a><div><br/>";

echo "<div itemprop='dataset' itemscope='' itemtype='http://schema.org/DataSet'>
			<a href = 'pages?file=" . $low . "' itemprop='url'>Previous</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href = 'pages?file=" . $high ."' itemprop='url'>Next</a><br/>
			<meta itemprop='measurementTechnique' content='Page Logging'>
			<meta itemprop='sameAs' content='" . "Logs/4Chan_Bans_Log-Reverse_Chrono-$file_no.json" . "'>
			<meta itemprop='version' content='" . $file_no . "' id='versionInfo'/>
			<table id='bansTable' class='table'>
				<tbody>
";

echo "<meta itemprop='variableMeasured' content='Board'>";
echo "<meta itemprop='variableMeasured' content='Name'>";
echo "<meta itemprop='variableMeasured' content='Comment'>";
echo "<meta itemprop='variableMeasured' content='Action'>";
echo "<meta itemprop='variableMeasured' content='Duration'>";
echo "<meta itemprop='variableMeasured' content='Reason'>";
echo "<meta itemprop='variableMeasured' content='Time'>";

$useragent=$_SERVER['HTTP_USER_AGENT'];
$is_mobile;
if(strpos($useragent, "Mobile") !== false) $is_mobile = true;

if($is_mobile){
	$font_size = 'font-size:12px';
	foreach(array_reverse($file_contents) as $line){
	echo "<tr>";																													
	if($line["board"] == "s4s") echo "<td>[" . $line["board"] ."]</td>";
	else echo "<td style = \"font-size:8px\">/" . $line["board"] ."/</td>";
		//echo "<td style = \"max-width:150px;$font_size\">" . $line["name"] ."</td>";
		//echo "<td style = \"max-width:100px;$font_size\">" . $line["trip"] ."</td>";
		echo "<td style = \"max-width:500px;$font_size\">" . $line["com"] ."</td>";
		echo "<td style='$font_size'>" . $line["action"] ."</td>";
		//echo "<td style='$font_size'>" . $line["length"] ."</td>";
		//echo "<td style='$font_size'>" . $line["reason"] ."</td>";
		//echo "<td style='$font_size'>" . $line["now"] ."</td>";
	echo "</tr>";
}
}
else{
	foreach(array_reverse($file_contents) as $line){
	echo "<tr>";																													
	if($line["board"] == "s4s") echo "<td>[" . $line["board"] ."]</td>";
	else echo "<td>/" . $line["board"] ."/</td>";
		echo "<td style = \"max-width:150px\">" . $line["name"] ."</td>";
		echo "<td style = \"max-width:100px\">" . $line["trip"] ."</td>";
		echo "<td style = \"max-width:800px\">" . $line["com"] ."</td>";
		echo "<td>" . $line["action"] ."</td>";
		echo "<td>" . $line["length"] ."</td>";
		echo "<td>" . $line["reason"] ."</td>";
		echo "<td>" . $line["now"] ."</td>";
	echo "</tr>";
}
}


echo "</tbody></table></div>";

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