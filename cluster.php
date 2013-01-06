<?php

function callR( $r, $a = "" )
{
	$cmd = escapeshellcmd( "Rscript $r $a" );
    return `$cmd`;
}
require_once("merge.php");

$file = "files/merge.tsv";
$method = $_POST['method'];
$metric = $_POST['metric'];
$output = $_POST['output'];
$title  = $_POST['name'];

$rArgs = "$file $method $metric $output \"$title\"";

$stdout= callR( "clustr.r", "$rArgs" );

$stdout=explode(",<r>,",$stdout);
	
$file = $stdout[0];

$out = openfile( $file, "b" );

?>