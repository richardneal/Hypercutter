<?php
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
function array_subset( $array, $start, $end ) 
{
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
		$wordcount["$word"] = isset($wordcount["$word"]) ? $wordcount["$word"] + 1 : 1;
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
?>