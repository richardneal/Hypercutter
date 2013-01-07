<?php
session_start();
	// For testing, automatically empty the chunks folder on load
/*
	$files = glob('files/chunks/*'); // get all file names
	foreach($files as $file) {
		if(is_file($file))
		unlink($file); // delete file
	}
	$files = glob('files/r/*'); // get all file names
	foreach($files as $file) {
		if(is_file($file))
		unlink($file); // delete file
	}
	$files = glob('tsvs/r/*'); // get all file names
	foreach($files as $file) {
		if(is_file($file))
		unlink($file); // delete file
	}
	if(is_file('files/merge.tsv'))
	unlink('files/merge.tsv');

	if(is_file('chunks.zip'))
	unlink('chunks.zip');
	*/

// If user clicked start over destroy the session and delete uploads
if (isset($_GET['action']) && $_GET['action'] == "clear") {
	unset($_SESSION['uploaded_files']);

	$folders = glob('sessions/' . session_id() . '/*');

	foreach ($folders as $folder) {
		if(is_dir($folder)) {
			$files = glob($folder . '/*');
			foreach($files as $file) {
				if(is_file($file))
				unlink($file); // delete file
			}
			rmdir($folder);
		}
		else {
			unlink($folder);
		}
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Hypercutter</title>
<link rel="stylesheet" type="text/css" media="all" href="styles.css" />
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<script type='text/javascript' src='https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js'></script>
<link rel="stylesheet" href="http://code.jquery.com/ui/1.9.2/themes/base/jquery-ui.css" type="text/css" media="all" />

<script src="tooltips.js"></script>
<script>
 $(function() {
        $( "#dialog-modal" ).dialog({
			autoOpen: false,
            height: 500,
			width: 700,
            modal: true,
            resizable: false,
			show: 'slide',
			hide: 'drop'
        });
		$('#about').click(function(){ $('#dialog-modal').dialog('open'); });
    });

$(function() {
        var file = $( "#swfileselect" );
 
        $( "#dialog-stopwords" ).dialog({
            autoOpen: false,
            height: 300,
            width: 400,
            modal: true,
			show: 'bounce',
			hide: 'puff',
            buttons: [{
				//id: 'swupload',
				//text: 'Upload'
				//,
				//click:
				//	function(){
						//alert("Stopword list: " + //document.getElementById('stopwordlist').value);
						//$( this ).dialog( "close" );
				//	}
				//},
				//{
				text: 'Delete List',
				click:
					function() {
						$("#stopwordlist").val("");
						$(document).data("swlist", "");
						$("#swmessages").empty();
						//$( this ).dialog( "close" );
					}
				}
			]
        });
 
        $('#stopwords').click(function(){ $('#dialog-stopwords').dialog('open'); });
    });
	
 $(function() {
        $( "#cluster-modal" ).dialog({
			autoOpen: false,
            height: 400,
			width: 700,
            modal: true,
            resizable: false,
			show: 'scale',
			hide: 'scale'
        });
		$('#cluster').click(function(){ $('#cluster-modal').dialog('open'); });
    });
</script>
</head>
<body>
<div id="wrap">
<h1>Hypercutter</h1>
<table>
<tr>
<td><button id="about">About This Tool</button></td>
<?php

require_once("cut.php");

if(isset($_SESSION['uploaded_files'])) {
// Script generates Strict Standards: Only variables should be passed by reference unless error reporting is changed.
//error_reporting(4);

// Replace this with file upload script
$textarray = array("Was","this","the","face","that","launched","a","thousand","ships");
$directory = "hypercutter/sessions/" . session_id() . "/uploads/" ;
$chunksize = $_SESSION['chunksize'];
$chunknumber = $_SESSION['chunknumber'];
$shiftsize = $_SESSION['shiftsize'];
$lastprop = $_SESSION['lastprop'];
/*

// Grab all the filenames ending in .txt
foreach (glob("$directory/*.txt") as $filename) {
	// For each file, get the contents as $data
	$data = file_get_contents($filename);
	$textarray = explode(" ", $data);

	// call the cutter function on the text array
	$chunkarray = cutter($textarray, $chunksize, $shiftsize, $lastprop);
	// write a new file for each chunk
	// separate function needed to calculate data
	// write a csv with the format
// AncreneWisse_2000_words_05_02000.txt	2000	608	357
// RANK	WORD	COUNT	"RELATIVE FREQUENCY"
// 1	þe	145	0.0725
// cf. chunkset.php

}

	*/

echo '<td><form action="index.php?action=clear" method="POST">
<input type="submit" value="Start Over"/>
</form></td></tr></table><p>
<fieldset><legend>Download</legend>
<table><tr>
<td><button id="cluster">Dendogram</button></td>
<td><form action="chunks.php" method="POST">
<input type="submit" value="Chunks"/>
</form></td>
<td><form id="download_tsv" action="download_tsv.php" method="POST">
<input type="submit" value="Merged TSV"/>
<input name="transpose" type="checkbox" checked/> <label>Transpose</label>
</form></td>
</tr>
</table>
</fieldset></p>';

echo "<hr>";
echo "<table width=\"600\">";
echo "<tr><td colspan=\"3\"><b>Options:</b></td></tr>";
echo "<tr><td width=\"200\">", ($_SESSION['chunksize'] ? "Chunk Size: " . $_SESSION['chunksize'] : "Number of Chunks: " . $_SESSION['chunknumber']) . "</td>";
echo "<td width=\"200\">Overlap: " . $_SESSION['shiftsize'] . "</td>";
echo ($_SESSION['chunksize'] ? "<td width=\"200\">Last Proportion: " . $_SESSION['lastprop'] * 100 . "%</td></tr>" : "</tr>");
echo "<tr><td width=\"100\">Keep Punctuation: " . $_SESSION['punctuation'] . "</td>";
echo "<td width=\"100\">Keep Apostrophes: " . $_SESSION['apostrophes'] . "</td>";
echo "<td width=\"100\">Keep Hyphens: " . $_SESSION['hyphens'] . "</td></tr>";
echo "<tr><td width=\"200\">Keep Numbers: " . $_SESSION['numbers'] . "</td>";
echo "<td colspan=\"2\">Preserve Case: " . $_SESSION['preserve_case'] . "</td></tr>";
if ($_SESSION['stopwordlist'] != "none") {
	$stopwords = $_SESSION['stopwordlist'];
	echo "<tr><td colspan=\"3\">Stopwords removed (<a href=\"#\" onclick=\"alert('" . $stopwords . "')\">View List</a>)</td></tr>";
}
echo "</table>";
echo "<hr>";

// Loop through the source files and chunk each one.
foreach ($_SESSION['uploaded_files'] as $sourcefile) {
	echo "<h3>".$sourcefile."</h3>";
	$text = file_get_contents('sessions/' . session_id() . '/uploads/' . $sourcefile);

	// Scrub the text
	// Replace accented characters
	$search = explode(",","ç,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø");
	$replace = explode(",","c,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o");	
	$text = str_replace($search, $replace, $text);
	
		// Remove stopwords
	if ($_SESSION['stopwordlist'] != "none") {
		$text = str_replace($_SESSION['stopwordlist'], " ", $text);
	}

	// Convert to lowercase
	if ($_SESSION['preserve_case'] != "yes") {
		$text = mb_convert_case($text, MB_CASE_LOWER, "UTF-8");
	}

	// Remove numbers
	if ($_SESSION['numbers'] != "yes") {
		$text = preg_replace("/[0-9]+/", ' ', $text);
	}
	
	// Remove punctuation
	$apos = $_SESSION['apostrophes'];
	$hyphens = $_SESSION['hyphens'];
	switch(true) {
	case $apos == 'no' && $hyphens == 'yes': // Remove all punctuation except hyphens
		$text = trim(preg_replace("#((?![-])\pP)+#", ' ', $text));
	break;
	case $apos == 'yes' && $hyphens == 'no': // Remove all punctuation except apostrophes
		$text = trim(preg_replace("#((?!['])\pP)+#", ' ', $text));
	break;
	case $apos == 'yes' && $hyphens == 'yes': // Remove all punctuation except apostrophes and hyphens
		$text = trim(preg_replace("#((?!['-])\pP)+#", ' ', $text));
	break;
	default: // Remove all punctuation
        $text = trim(preg_replace("#[^\p{L}\p{N}]+#u", ' ', $text));
	}

	// Make the text an array and chunk it
	$textarray = explode(" ", $text);

	if ($_SESSION['chunkoption'] == "size") {
		$chunkarray = cutter($textarray, $chunksize, $shiftsize, $lastprop);
		$chunknumber = count($chunkarray);
	}
	else {
		$chunksize = ceil(count($textarray)/$chunknumber);
		$chunkarray = cutter($textarray, $chunksize, $chunksize, 0);
	}


// Build the output
$i = 1;
$padlength = intval(log10(count($chunkarray))) + 1;
foreach ($chunkarray as $range=>$tokens) {
 	$outrange = str_replace("..", "-", $range);
	$printrange = str_replace("..", " to ", $range);
	$outfile = rtrim($sourcefile, ".txt") . str_pad($i, $padlength, "0", STR_PAD_LEFT) . "_" . $outrange;
	$header = "Chunk " . str_pad($i, $padlength, "0", STR_PAD_LEFT) . ": Tokens " . $printrange . " (" . $outfile . ")";
	echo "<b>" . $header . "</b><br>";
	$str = implode(" ", $tokens);
	echo $str;
	echo "<hr>";
	$i++;
	// Write the header and string to a file here.
	$out = $header . "\n" . $str;
	if (!is_dir('sessions/' . session_id() . '/chunks/')) {
		mkdir('sessions/' . session_id() . '/chunks/');
	}
	$outdirectory = 'sessions/' . session_id() . '/chunks/'; // Needs a directory path
	$chunkfile = $outdirectory . $outfile . ".txt";
	file_put_contents($chunkfile, $out);



	$wordcount = count_words($tokens);
	if (!is_dir('sessions/' . session_id() . "/tsvs/")) {
		mkdir('sessions/' . session_id() . "/tsvs/");
	}
	$tsvdirectory = "sessions/" . session_id() . "/tsvs/";
	hash_sort($wordcount, 'c');
	$outtsv = http_build_query($wordcount, '', ',');
	$tsvfile = $tsvdirectory . $outfile . ".tsv";
	file_put_contents($tsvfile, $outtsv);

}

}
echo "<hr><hr>";

// Output generated, so delete the file uploads
$files = glob('hypercutter/sessions/' . session_id() . '/uploads/*'); // get all file names
foreach($files as $file) {
	if(is_file($file))
	unlink($file); // delete file
}
// No session variable
} else {
?>
</tr>
</table>
<p></p>
<form id="upload" action="upload.php" method="POST" enctype="multipart/form-data" onSubmit="return nochunksize();">

<fieldset>
<legend>Bulk File Upload</legend>

<input type="hidden" id="MAX_FILE_SIZE" name="MAX_FILE_SIZE" value="1000000" />
<div>
	<label for="fileselect">Files to upload:</label>
	<input type="file" id="fileselect" name="fileselect[]" multiple="multiple" />
	<div id="filedrag">Or drop files here</div>
</div>

</fieldset>

<fieldset>
<legend>Scrubbing Options</legend>
<table width="350">
<tr><td colspan="2"><input name="punctuation" type="checkbox" value="<?php echo isset($_SESSION['punctuation']) ? $_SESSION['punctuation'] = 'yes' : $_SESSION['punctuation'] = 'no'; ?>"/> <label for="punctuation" value="yes">Keep Punctuation</label></td></tr>
<tr><td width="50%"><input name="apostrophes" type="checkbox" value="<?php echo isset($_SESSION['apostrophes']) ? $_SESSION['apostrophes'] = 'yes' : $_SESSION['apostrophes'] = 'no'; ?>"/> <label>Keep Apostrophes</label></td>
<td width="50%"><input name="hyphens" type="checkbox" value="<?php echo isset($_SESSION['hyphens']) ? $_SESSION['hyphens'] = 'yes' : $_SESSION['hyphens'] = 'no'; ?>"/> <label>Keep Hyphens</label></td></tr>
<tr><td width="50%"><input name="numbers" type="checkbox" value="<?php echo isset($_SESSION['numbers']) ? $_SESSION['numbers'] = 'yes' : $_SESSION['numbers'] = 'no'; ?>"/> <label for="numbers">Keep Numbers</label></td>
<td width="50%"><input name="preserve_case" type="checkbox" value="<?php echo isset($_SESSION['preserve_case']) ? $_SESSION['preserve_case'] = 'yes' : $_SESSION['preserve_case'] = 'no'; ?>"/> <label for="preserve_case">Preserve Case</label></td></tr>
<tr><td width="50%"><a id="stopwords" href="#">Remove Stopwords</a> <img valign="bottom" src="question_mark.png" alt="Question Mark" title="Click the link to upload a stopword list (a text file with each stopword separated by a space or a comma)." /></td>
<td width="50%"><a id="advanced" href="#">Advanced Options</a> <img valign="bottom" src="question_mark.png" alt="Question Mark" title="Advanced options allow you to upload a list of token-lemma equivalents, a list of word or character equivalents to consolidate, rules for handling special characters, or custom regular expressions." /></td></tr>
</table>
<input type="hidden" id="stopwordlist" name="stopwordlist" value="" />
</fieldset>

<fieldset>
<legend>Chunk Settings</legend>
<label>Split by:</label>
<input type="radio" name="chunkoption" value="size" checked onClick="hidechunkoption(1);">Chunk Size
<input type="radio" name="chunkoption" value="number" onClick="hidechunkoption(2);">Number of Chunks
<p><label>Chunk Size:</label> <input name="chunksize" id="chunksize" type="text" size="12"/> (No. words per chunk)</p>
<p><label>Number of Chunks:</label> <input name="chunknumber" id="chunknumber" type="text" size="12" disabled/></p>
<p><label>Overlap:</label> <input name="shiftsize" type="text" size="12" value="0"/> (No. words overlapping at chunk boundaries)</p>
<p><label>Last Proportion:</label> <input name="lastprop" id="lastprop" type="text" size="3" value="50"/>% (The proportion of chunksize the last chunk can be)</p>
</fieldset>

<p><input type="submit" value="Scrub &amp; Chunk Files"/></p>

</form>

<div id="progress"></div>

<div id="messages">
<p>Status Messages</p>
</div>

<script src="filedrag.js"></script>

<?php
}
?>
<div id="dialog-modal" title="About Hypercutter">
    <p>Hypercutter streamlines some of the functions of Scrubber and Divitext. It allows you to set some basic scrubbing and chunking options, then upload multiple files to be processed. The file upload function is wrapped in an <a href="http://blogs.sitepointstatic.com/examples/tech/filedrag/3/index.html" target="_blank">HTML5 file drag &amp; drop API</a> that uses asynchronous Ajax file uploads, graphical progress bars, and progressive enhancement. Note that it does not work in Internet Explorer. After selecting files, wait until the green progress bar shows that they have been uploaded.  It will flick to bright, lime green when ready. Then click the "Scrub &amp; Chunk Files" button. The files will be scrubbed and chunked according to your criteria. You may then download the chunked files by clicking the "Download Chunks" link. Click the "Start Over" to upload a new set of texts. <b>When you are done, please click "Start Over" to clear the "chunks" folder.</b></p>
	<p>Notes:</p>
	<ol>
        <li>The zip archive download script seems to be very unpredictable, often generating empty archives. I got it working right before bed, but no guarantees...</li>
        <li>For some reason, the web output is showing the chunks twice, although it's not too annoying, as it goes through all texts before starting over. The downloaded texts are correct.</li>
	<li>The scrubbing functions remove all non-alphabetic characters, change accented characters to non-accented ones (e.g. <i>&eacute;</i> becomes <i>e</i>), convert to lowercase, and create an array of tokens. The text is assumed to be in UTF-8, and the script handles <i>&eth;</i>, <i>&thorn;</i>, <i>&aelig;</i>, and <i>&#540;</i> quite well. There is currently no provision for texts containing entities like "&amp;eth;" The ampersands and semicolons will be stripped.</li>
	<li>Most of the code for chunking is copied from Divitext. Divitext's $shiftsize variable is marshalled to create overlaps. The user-supplied chunk size minus overlap = $shiftsize. So 100-word chunks with an overlap of 90 will cause chunks to overlap by 10 words.</li>
	<li>Maximum file size is 1,000,000 bytes, but it can be changed.</li>
	<li>The percentage entered for the last proportion is divided by 100 (e.g. 50% becomes .5). I hope that's right.</li>
	<li>Files are uploaded into an "uploads" folder, which is emptied once the output is generated. Chunks are saved in a "chunks" folder, which is also emptied when the chunks are downloaded, when you click "Start Over", or when the tool is reloaded with a new session. This obviously has to be re-written with user-specific sessions and folders to prevent conflicts.</li>
        <li>A better progress bar is needed because it is hard to see when the fade-in one is finished.</li>
	<li>The downloadable zip archive only contains files of chunked texts. The next step will be to generate the stats &agrave; la Divitext.</li>
	</ol>
</div>

		<div id="dialog-stopwords" title="Upload Stopword List">
		
				<label for="swfileselect">File to upload:</label>
				<input type="file" id="swfileselect" name="swfileselect[]" />
				<div id="swfiledrag"></div>
		
			<div id="swprogress"></div>

			<div id="swmessages" style="font-size:10px;">
				<!--<p>Status Messages</p>-->
			</div>
		</div>
			<!-- Filedrag Script -->
			<script src="templates/js/swfiledrag.js"></script>

<div id="cluster-modal" title="Generate Dendogram">
    <form id="cluster" action="cluster.php" method="POST">

	<fieldset>
	<legend>Dendogram Options</legend>
	
	<p><label>Name:</label> <input name="name" type="text" size="12"/></p>
	<p><label>Linkage Method:</label>
	<select name="method">
  		<option value="average">Average</option>
		<option value="ward">Ward</option>
		<option value="single">Single</option>
		<option value="complete">Complete</option>
		<option value="mcquitty">McQuitty</option>
		<option value="median">Median</option>
		<option value="centroid">Centroid</option>
	</select>
	<p><label>Distance Metric:</label>
	<select name="metric">
  		<option value="euclidean">Euclidean</option>
		<option value="maximum">Maximum</option>
		<option value="manhattan">Manhattan</option>
		<option value="canberra">Canberra</option>
		<option value="binary">Binary</option>
		<option value="minkowski">Minkowski</option>
	</select>
	<p><label>Clustering Output Type:</label>
	<select name="output">
  		<option value="pdf">PDF</option>
		<option value="phyloxml">PhyloXML</option>
	</select>
	</fieldset>
	<p><input type="submit" value="Get Dendogram"/></p>
	</form>
</div>

</div>

<script type="text/javascript">
function nochunksize()
{
	if (document.getElementById('chunksize').value == '' && document.getElementById('chunknumber').value == '')
	{
		alert('Please enter a chunk size or a number of chunks.')
		return false;
	}
}
function hidechunkoption(num) {

    switch(num)
    {
    	case 1:
    		var show = document.getElementById("chunksize");
    		var hide = document.getElementById("chunknumber");
    		break;
    	case 2:
    		var hide = document.getElementById("chunksize");
    		var show = document.getElementById("chunknumber");
    		break;
    }
    hide.disabled = true;
    hide.value = "";
    show.disabled = false;

    var proportion = document.getElementById("lastprop");
    proportion.disabled ? (proportion.disabled = false, proportion.value = '50') : (proportion.disabled = true, proportion.value = '');
}
</script>

</body>
</html>