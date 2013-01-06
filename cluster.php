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

function downloadString( $str, $name = "defaultname", $ext = null, $utf8 = true ){
 // Must be fresh start 
  if( headers_sent() ) 
    die('Headers Sent'); 

  // Required for some browsers 
  if(ini_get('zlib.output_compression')) 
    ini_set('zlib.output_compression', 'Off'); 

    // Determine Content Type 
    switch ($ext) { 
      case "pdf": $ctype="application/pdf"; break; 
      case "exe": $ctype="application/octet-stream"; break; 
      case "zip": $ctype="application/zip"; break; 
      case "doc": $ctype="application/msword"; break; 
      case "xls": $ctype="application/vnd.ms-excel"; break; 
      case "ppt": $ctype="application/vnd.ms-powerpoint"; break; 
      case "gif": $ctype="image/gif"; break; 
      case "png": $ctype="image/png"; break; 
      case "jpeg": 
      case "jpg": $ctype="image/jpg"; break; 
      default: $ctype="application/force-download"; 
    } 

    header("Pragma: public"); // required 
    header("Expires: 0"); 
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
    header("Cache-Control: private",false); // required for certain browsers 
    header("Content-Type: $ctype"); 
    header("Content-Disposition: attachment;
    filename=\"".$name."\";" ); 
    header("Content-Transfer-Encoding: binary"); 
    ob_clean(); 
	flush(); 
    if ( $utf8 )
        echo utf8_decode( $str );
    else
        echo $str;
}

require_once("merge.php");

transpose();

if (!is_dir('sessions/' . session_id() . '/r/')) {
    mkdir('sessions/' . session_id() . '/r/');
}

$directory = getcwd() . "/sessions/" . session_id() . "/r/";
$file = getcwd() . "/sessions/" . session_id() . "/merge.tsv";
//$file = getcwd() . "/mergediv.tsv";
$method = $_POST['method'];
$metric = $_POST['metric'];
$output = $_POST['output'];
$title  = $_POST['name'];
$p = 2;
$type = "tsv";
$labelfile = NULL;
$scrubtags = " ";
$divitags = " ";

$rArgs = "$file $method $metric $output \"$title\" $p $type $labelfile $scrubtags $divitags $directory";

$stdout= callR( "clustr.r", "$rArgs" );

$stdout=explode(",<r>,",$stdout);
	
$file = $stdout[0];

$out = openfile( $file, "b" );

downloadString($out,"$title.pdf","pdf",false);

?>