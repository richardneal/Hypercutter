<?php
//unlink("chunks.zip");
$zip = new ZipArchive();
$filename = "chunks.zip";

if ($zip->open($filename, ZIPARCHIVE::CREATE)!==TRUE) {
    exit("cannot open <$filename>\n");
}

$files = glob('files/chunks/*'); // get all file names
foreach ($files as $file) {
	$zip->addFile($file);
	//if(is_file($file)) { // Delete the file after adding to the archive
	//	unlink($file);
	//}
}
$zip->close();

// http://perishablepress.com/press/2010/11/17/http-headers-file-downloads/

// set example variables
$filepath = "";

// http headers for zip downloads
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: public");
header("Content-Description: File Transfer");
header("Content-type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"".$filename."\"");
header("Content-Transfer-Encoding: binary");
header("Content-Length: ".filesize($filepath.$filename));
while (ob_get_level()) {
     ob_end_clean();
}
//ob_end_flush();
@readfile($filepath.$filename);
?>