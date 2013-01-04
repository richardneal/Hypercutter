<?php
session_start();
	// For testing, automatically empty the chunks folder on load
	$files = glob('files/chunks/*'); // get all file names
	foreach($files as $file) {
		if(is_file($file))
		unlink($file); // delete file
	}	
// If user clicked start over destroy the session and delete uploads
if (isset($_GET['action']) && $_GET['action'] == "clear") {
	unset($_SESSION['uploaded_files']);
	$files = glob('uploads/*'); // get all file names
	foreach($files as $file) {
		if(is_file($file))
		unlink($file); // delete file
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
			show: 'slide',
			hide: 'drop'
        });
		$('#about').click(function(){ $('#dialog-modal').dialog('open'); });
    });
</script>
</head>
<body>
<div id="wrap">
<h1>Hypercutter</h1>
<p><button id="about">About This Tool</button></p>
<?php

if(isset($_SESSION['uploaded_files'])) {
// Script generates Strict Standards: Only variables should be passed by reference unless error reporting is changed.
error_reporting(4);

// Replace this with file upload script
$textarray = array("Was","this","the","face","that","launched","a","thousand","ships");
$directory = "hypercutter/uploads";
$chunksize = $_SESSION['chunksize'];
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

echo '<p><a href="index.php?action=clear">Start Over</a>&nbsp;&nbsp;
<a href="chunks.php">Download Chunks</a></p>';

echo "<hr>";
echo "<table width=\"600\"><tr><td colspan=\"3\"><b>Options:</b></td></tr><tr><td>Chunk Size: " . $_SESSION['chunksize'] . "</td>";
echo "<td width=\"200\">Overlap: " . $_SESSION['shiftsize'] . "</td>";
echo "<td width=\"200\">Last Proportion: " . $_SESSION['lastprop'] * 100 . "%</td></tr>";
echo "<tr><td width=\"200\">Keep Apostrophes: " . $_SESSION['apostrophes'] . "</td>";
echo "<td colspan=\"2\">Keep Hyphens: " . $_SESSION['hyphens'] . "</td></tr></table>";
echo "<hr>";

// Loop through the source files and chunk each one.
foreach ($_SESSION['uploaded_files'] as $sourcefile) {
	echo "<h3>".$sourcefile."</h3>";
	$text = file_get_contents("uploads/".$sourcefile);

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
	$chunkarray = cutter($textarray, $chunksize, $shiftsize, $lastprop);

// Build the output
$i = 1;
foreach ($chunkarray as $range=>$tokens) {
 	$outrange = str_replace("..", "-", $range);
	$printrange = str_replace("..", " to ", $range);
	$outfile = rtrim($sourcefile, ".txt") . $i . "_" . $outrange . ".txt";
	$header = "Chunk " . $i . ": Tokens " . $printrange . " (" . $outfile . ")";
	echo "<b>" . $header . "</b><br>";
	$str = implode(" ", $tokens);
	echo $str;
	echo "<hr>";
	$i++;
	// Write the header and string to a file here.
	$out = $header . "\n" . $str;
	$outdirectory = "files/chunks/"; // Needs a directory path
	$outfile = $outdirectory . $outfile;
	file_put_contents($outfile, $out);		
}

}
echo "<hr><hr>";

// Output generated, so delete the file uploads
$files = glob('hypercutter/uploads/*'); // get all file names
foreach($files as $file) {
	if(is_file($file))
	unlink($file); // delete file
}
// No session variable
} else {
?>

<form id="upload" action="upload.php" method="POST" enctype="multipart/form-data">

<fieldset>
<legend>Scrubbing Options</legend>
<table width="350">
<tr>
<td width="50%"><input name="apostrophes" type="checkbox" value="<?php echo isset($_SESSION['apostrophes']) ? $_SESSION['apostrophes'] = 'yes' : $_SESSION['apostrophes'] = 'no'; ?>"/> <label for="apostrophes">Keep Apostrophes</label></td>
<td width="50%"><input name="hyphens" type="checkbox" value="<?php echo isset($_SESSION['hyphens']) ? $_SESSION['hyphens'] = 'yes' : $_SESSION['hyphens'] = 'no'; ?>"/> <label for="hyphens">Keep Hyphens</label></td>
</tr>
</table>
</fieldset>

<fieldset>
<legend>Chunk Settings</legend>
<p><label for="chunksize">Chunk Size:</label> <input name="chunksize" type="text" size="12"/> (No. words per chunk)</p>
<p><label for="shiftsize">Overlap:</label> <input name="shiftsize" type="text" size="12" value="0"/> (No. words overlapping at chunk boundaries)</p>
<p><label for="lastprop">Last Proportion:</label> <input name="lastprop" type="text" size="3" value="50"/>% (The proportion of chunksize the last chunk can be)</p>
</fieldset>

<fieldset>
<legend>Bulk File Upload</legend>

<input type="hidden" id="MAX_FILE_SIZE" name="MAX_FILE_SIZE" value="1000000" />
<div>
	<label for="fileselect">Files to upload:</label>
	<input type="file" id="fileselect" name="fileselect[]" multiple="multiple" />
	<div id="filedrag">Or drop files here</div>
</div>

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

</div>
</body>
</html>