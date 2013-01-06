<?php
require_once("merge.php");
session_start();

if (isset($_POST["transpose"])) {
	transpose();
}
else {
	merge();
}

header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: public");
header("Content-Description: File Transfer");
header("Content-type: text");
header("Content-Disposition: attachment; filename=\"merge.tsv\"");
header("Content-Transfer-Encoding: binary");
while (ob_get_level()) {
     ob_end_clean();
}
@readfile("files/merge.tsv");

?>