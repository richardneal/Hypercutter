<?php
session_start();
	// For testing, automatically empty the chunks folder on load
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

// If user clicked start over destroy the session and delete uploads
if (isset($_GET['action']) && $_GET['action'] == "clear") {
	unset($_SESSION['uploaded_files']);
	$files = glob('uploads/' . session_id() . '/*'); // get all file names
	foreach($files as $file) {
		if(is_file($file))
		unlink($file); // delete file
	}
	if (is_dir('uploads/' . session_id())) {
		rmdir('uploads/' . session_id());
	}

	$files = glob('files/chunks/' . session_id() . '/*'); // get all file names
	foreach($files as $file) {
		if(is_file($file))
		unlink($file); // delete file
	}
	if (is_dir('files/chunks/' . session_id())) {
		rmdir('files/chunks/' . session_id());
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Hypercutter</title>
<link rel="stylesheet" type="text/css" media="all" href="styles.css" />
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script type='text/javascript' src='https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.21/jquery-ui.min.js?ver=1.8.21'></script>
<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.17/themes/base/jquery-ui.css" type="text/css" media="all" />
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

if(isset($_SESSION['uploaded_files'])) {
// Script generates Strict Standards: Only variables should be passed by reference unless error reporting is changed.
//error_reporting(4);

// Replace this with file upload script
$textarray = array("Was","this","the","face","that","launched","a","thousand","ships");
$directory = "hypercutter/uploads/" . session_id();
$chunksize = $_SESSION['chunksize'];
$chunknumber = $_SESSION['chunknumber'];
$shiftsize = $_SESSION['shiftsize'];
$lastprop = $_SESSION['lastprop'];

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

	// cutter()
	// cuts the input array into specified chunks
	// ARGS:
	//	$textarray: array containing the words of the text in
	//		individual slots
	// 	$chunksize: the size in words of a chunk
	//	$shiftsize: the number of words to shift
	// 	$lastprop: the proportion of a chunk the last chunk can be
	// RETURN: an array of chunks, where a chunk is a subset of 
	//		the input array indexed by the first and last word number
	//		in the chunk, each chunk will not necessarily be indexed
	//		by word number, but will be textual order
	function cutter( $textarray, $chunksize, $shiftsize, $lastprop ) {

		// set initial chunk
		$start = 0;
		$end = $chunksize;
		// grab the next chunk and add it in if the bounds were not exceeded
		while ( $chunk = array_subset( $textarray, $start, $end ) ) {

			// create the index of the $start..$end, most of the time,
			// if the subset came back having stopped at MAX, we'll
			// need the last key in the $chunk array
			$index = "$start.." . array_pop( array_keys( $chunk ) );
			$chunkarray[$index] = $chunk;

			// get new bounds
			$start += $shiftsize;
			$end += $shiftsize;

		}

		// determine the min size of the last chunk
		// err on the side of too much; better to have a chunk of
		// 4 in chunksize 3 than a chunk of 1
		$lastsize = ceil( $chunksize * $lastprop );

		// find the last chunk
		$lastchunk = end( $chunkarray );

		// the the size of the last chunk is smaller than allowed, 
		//  merge and reindex
		if ( count( $lastchunk ) < $lastsize ) {
			
			// discard the offending chunk
			array_pop( $chunkarray );

			// get the very final index of the last chunk, last word
			$indexend = array_pop( array_keys( $lastchunk ) );

			// remove and capture the chunk to append to
			$secondlast = array_pop( $chunkarray );

			// get the first index of that array and prepend 
			//  that to create the new index to $chunkarray
			$index = array_shift( array_keys( $secondlast ) ) . "..$indexend";
		
			// merge the two chunks in order, and stick it on
			$newchunk = array_merge( $secondlast, $lastchunk );
			$chunkarray[$index] = $newchunk;

		}

		return $chunkarray;

	}

	// array_subset()
	// dumb PHP doesn't have this function, so this is a hacky
	// version that doesn't think about non-numeric keys
	// ARGS:
	//	$array: array indexed by numbers
	//	$start: index of first element in subset
	//	$end: index of first element not in array
	// RETURN: an array [$start,$end) from $array with
	//		the same indicies 
	function array_subset( $array, $start, $end ) {
	
		$MAX = count( $array );
		//$subset = array();
		for ( $i = $start; $i < $end; $i++ ) {

			if ( $i >= $MAX )
				break;
			$subset[$i] = $array[$i];

		}

		if ( count( $subset ) == 0 )
			return null;
		else
			return $subset;

	}


	function count_words( &$textarray ) 
	{
	    $wordcount = array();
	    // iterate through the array of words and up the count
	    foreach ( $textarray as $word ) 
	    {
	        if ( $word == "" )
	            continue;
	        $wordcount[ "$word" ] = isset( $wordcount[ "$word" ] ) ? 
	                $wordcount[ "$word" ] + 1 : 1;
	    }

	    return $wordcount;

	}

	function hash_sort( &$hash, $sort ) {

    if ( $sort == 'c' ) {

        // grab array for word and counts
        $word = array_keys( $hash );
        $count = array_values( $hash );
        
        // sort the counts, then words in $hash
        array_multisort( $count, SORT_DESC, $word, SORT_ASC, $hash );

    }
    else {

        // sort by key, ie. the word name
        ksort( $hash );

    }

}

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
	$text = file_get_contents('uploads/'.session_id().'/'.$sourcefile);

	// Scrub the text
	// Replace accented characters
	$search = explode(",","ç,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø");
	$replace = explode(",","c,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o");
	$text = str_replace($search, $replace, $text);
	// Convert to lowercase
	$text = mb_convert_case($text, MB_CASE_LOWER, "UTF-8");
	// Remove punctuation
	switch(true) {
	case $_SESSION['apostrophes'] == 'no' && $_SESSION['hyphens'] == 'yes': // Remove all punctuation except hyphens
		$text = trim(preg_replace("#((?![\-])\pP)+#", ' ', $text));
	break;
	case $_SESSION['apostrophes'] == 'yes' && $_SESSION['hyphens'] == 'no': // Remove all punctuation except apostrophes
		$text = trim(preg_replace("#((?![\'])\pP)+#", ' ', $text));
	break;
	case $_SESSION['apostrophes'] == 'yes' && $_SESSION['hyphens'] == 'yes': // Remove all punctuation except apostrophes and hyphens
		$text = trim(preg_replace("#((?![\'\-])\pP)+#", ' ', $text));
	break;
	default: // Remove all punctuation
              $text = trim(preg_replace("#((?)\pP)+#", ' ', $text));
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


	/*
	foreach ($chunkarray as $chunk) {
		$wordcount = count_words(&$chunk);
	}

	*/

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
	if (!is_dir('files/chunks/' . session_id())) {
		mkdir('files/chunks/' . session_id());
	}
	$outdirectory = "files/chunks/" . session_id() . "/"; // Needs a directory path
	$chunkfile = $outdirectory . $outfile . ".txt";
	file_put_contents($chunkfile, $out);



	$wordcount = count_words($tokens);
	if (!is_dir('files/tsvs/' . session_id())) {
		mkdir('files/tsvs/' . session_id());
	}
	$tsvdirectory = "files/tsvs/" . session_id() . "/";
	
	//$tsvarray = array();
	hash_sort($wordcount, 'c');
	$outtsv = http_build_query($wordcount, '', ',');
	/*
	foreach ($wordcount as $word => $count) {
		$tsvarray[] = array($word, $count);
	}
	*/
	$tsvfile = $tsvdirectory . $outfile . ".tsv";
	file_put_contents($tsvfile, $outtsv);

}

}
echo "<hr><hr>";

// Output generated, so delete the file uploads
$files = glob('hypercutter/uploads/' . session_id() . '/*'); // get all file names
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
<tr><td width="50%">&nbsp;&nbsp;&nbsp;<input name="apostrophes" type="checkbox" value="<?php echo isset($_SESSION['apostrophes']) ? $_SESSION['apostrophes'] = 'yes' : $_SESSION['apostrophes'] = 'no'; ?>"/> <label>Keep Apostrophes</label></td>
<td width="50%">&nbsp;&nbsp;&nbsp;<input name="hyphens" type="checkbox" value="<?php echo isset($_SESSION['hyphens']) ? $_SESSION['hyphens'] = 'yes' : $_SESSION['hyphens'] = 'no'; ?>"/> <label>Keep Hyphens</label></td></tr>
<tr><td width="50%"><input name="numbers" type="checkbox" value="<?php echo isset($_SESSION['numbers']) ? $_SESSION['numbers'] = 'yes' : $_SESSION['numbers'] = 'no'; ?>"/> <label for="numbers">Keep Numbers</label></td>
<td width="50%"><input name="preserve_case" type="checkbox" value="<?php echo isset($_SESSION['preserve_case']) ? $_SESSION['preserve_case'] = 'yes' : $_SESSION['preserve_case'] = 'no'; ?>"/> <label for="preserve_case">Preserve Case</label></td></tr>
<tr><td width="50%"><a id="stopwords" href="#">Remove Stopwords</a> <img valign="bottom" src="question_mark.png" alt="Question Mark" title="Click the link to upload stopword list (a text file with each stopword separated by a space or a comma)." /></td>
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