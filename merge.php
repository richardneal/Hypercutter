<?php
// This is the same as the original merge.php, but with commenting
session_start();

function transpose(){
	// Grab the filenames from the tsvs directory
	$all_tsvs = glob("sessions/" . session_id() . "/tsvs/*");
	// Initiate a hash table for all the file contents
	$all_hash = array();

	// Loop through the filenames
	foreach ($all_tsvs as $tsv_file)
	{
		// Get the text name
		$txtname = basename($tsv_file, ".tsv");
		// Add the text name as the key to the hash table
		$all_hash[$txtname] = array();
		// Read the file and assign it to a hash array
		$file = fopen($tsv_file, 'r');
		$hash = fgetcsv($file);
		// Loop through the hash array and get each set of contents
		foreach ($hash as $set) {
			// Get the words and counts for the current set by splitting around "="
			list($word, $count) = explode("=", $set);
			// Add a sub-array as word=>count to the $all_hash entry for the current text
			$all_hash[$txtname][$word] = $count;
		}
	}

	// Initiate $allwords as a variable
	$allwords = null;
	// Loop through the complete hash table
	foreach ($all_hash as $hash)
	{
		// Get the keys for each entry in the table
		$hashkeys = array_keys($hash);
		// Convert $allwords to an array and containing all the keys for all the texts
		$allwords = array_merge($hashkeys, (array)$allwords);
	}
	 // Get an array of unique keys for all the words
	$uniquewords = array_unique($allwords);
	// Begin the output labelled transposed
	$merge = "Transposed\t";
	// Add the list of unique words as a tab-separated string
	$merge .= implode("\t", $uniquewords) . "\n";
	// Loop through the hash of texts, assigning each key (textname) as chunkname and each value as an array of words and counts
	foreach ($all_hash as $chunkname => $hash) {
		$line = $chunkname; // Begin each row with the chunk name
		// Loop through the array of unique words
		foreach ($uniquewords as $word) {
			## Proceed only if there are no stopwords or if the word is not in the stopword list ##
			if (isset($_SESSION['stopwordlist']) && $_SESSION['stopwordorderbox'] == "off" && !in_array($word, $_SESSION['stopwordlist'])) {
				// Count the number of times the current word occurs in the current chunk
				$count = @$all_hash[$chunkname]["$word"];
				// If the word doesn't occur, make its count is zero
				$count = $count ? $count : 0;
				// Add the word counts as a tab-separated string
				$line .= "\t" . $count;
			}
		}
		// End the line when there are no more words
		$merge .= "$line\n";
	}
	// Write the $merge output as a file
	file_put_contents("sessions/" . session_id() . "/merge.tsv", $merge);
}

// Code is almost exactly the same as for transpose(). Only differences are highlighted with comments
function merge(){
	$all_tsvs = glob("sessions/" . session_id() . "/tsvs/*");
	$all_hash = array();

	foreach ($all_tsvs as $tsv_file)
	{
		$txtname = basename($tsv_file, ".tsv");
		$all_hash[$txtname] = array();
		$file = fopen($tsv_file, 'r');
		$hash = fgetcsv($file);
		foreach ($hash as $set) {
			list($word, $count) = explode("=", $set);
			$all_hash[$txtname][$word] = $count;
		}
	}

	$allwords = null;
	foreach ($all_hash as $hash)
	{
		$hashkeys = array_keys($hash);
		$allwords = array_merge($hashkeys, (array)$allwords);
	}
	$uniquewords = array_unique($allwords);
	$chunknames = array_keys($all_hash);
	$merge = "Key\t";
	$merge .= implode("\t", $chunknames) . "\n";
	foreach ($uniquewords as $word) { // Loop through the unique words directly
		## Proceed only if there are no stopwords or if the word is not in the stopword list ##
		if (isset($_SESSION['stopwordlist']) && $_SESSION['stopwordorderbox'] == "off" && !in_array($word, $_SESSION['stopwordlist'])) {
			$line = "$word"; // Begin each row with the word
			foreach ($chunknames as $chunk) {
				$count = @$all_hash[$chunkname]["$word"];
				$count = $count ? $count : 0;
				$line .= "\t" . $count;
			}
		}
		$merge .= "$line\n";
	}
	file_put_contents("sessions/" . session_id() . "/merge.tsv", $merge);
}


?>