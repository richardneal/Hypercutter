<?php

function callR( $r, $a = "" )
{
	$cmd = escapeshellcmd( "Rscript $r $a" );
    return `$cmd`;
}

function openfile( $f = "", $m = "" )
{
    $contents = "";
    if ( $f )
    {
        $FH = fopen( $f, "r$m" );
        $contents = fread( $FH, filesize( $f ) );
        fclose( $FH );
    }

    return $contents;
}


require_once("merge.php");

$file = "files/merge.tsv";
$method = $_POST['method'];
$metric = $_POST['metric'];
$output = $_POST['output'];
$title  = $_POST['name'];
$type = "tsv";

$rArgs = "$file $method $metric $output \"$title\" $type";

$stdout= callR( "clustr.r", "$rArgs" );

$stdout=explode(",<r>,",$stdout);
	
$file = $stdout[0];

$out = openfile( $file, "b" );

?>