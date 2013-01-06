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
$p = 2;
$type = "tsv";
$labelfile = NULL;
$scrubtags = " ";
$divitags = " ";

$rArgs = "$file $method $metric $output \"$title\" $p $type $labelFile $scrubtags $divitags";

$stdout= callR( "clustr.r", "$rArgs" );

$stdout=explode(",<r>,",$stdout);
	
$file = $stdout[0];

$out = openfile( $file, "b" );

?>