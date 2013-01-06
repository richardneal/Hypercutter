<?php
session_start();

function transpose(){
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
	$merge = "Transposed\t";
	$merge .= implode("\t", $uniquewords) . "\n";
	foreach ($all_hash as $chunkname => $hash) {
		$line = $chunkname;
		foreach ($uniquewords as $word) {
			$count = @$all_hash[$chunkname]["$word"];
			$count = $count ? $count : 0;
			$line .= "\t" . $count;
		}
		$merge .= "$line\n";
	}
	file_put_contents("sessions/" . session_id() . "/merge.tsv", $merge);
}

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
	foreach ($uniquewords as $word) {
		$line = "$word";
		foreach ($chunknames as $chunk) {
			$count = @$all_hash[$chunkname]["$word"];
			$count = $count ? $count : 0;
			$line .= "\t" . $count;
		}
		$merge .= "$line\n";
	}
	file_put_contents("sessions/" . session_id() . "/merge.tsv", $merge);
}


?>