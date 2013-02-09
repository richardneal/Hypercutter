<?php
/*
Server-side PHP file upload code for HTML5 File Drag & Drop demonstration
Featured on SitePoint.com
Developed by Craig Buckler (@craigbuckler) of OptimalWorks.net
*/
session_start();
$_SESSION['punctuationbox'] = $_POST['punctuationbox'];
$_SESSION['aposbox'] = $_POST['aposbox'];
$_SESSION['hyphensbox'] = $_POST['hyphensbox'];
$_SESSION['digitsbox'] = $_POST['digitsbox'];
$_SESSION['formattingbox'] = $_POST['formattingbox'];
$_SESSION['tags'] = $_POST['tags'];
$_SESSION['lowercasebox'] = $_POST['lowercasebox'];
$_SESSION['stopwordlist'] = $_POST['stopwordlist'];
$_SESSION['lemmalist'] = $_POST['lemmalist'];
$_SESSION['consolidationslist'] = $_POST['consolidationslist'];
/*
// Define the specialchars list either from an upload or a pre-defined set
if ($_POST['specialcharslist'] !="") {
	$_SESSION['specialcharslist'] = $_POST['specialcharslist'];
} else {
	$entityrulesfile = "entityrules/" . $_POST["entityrulesopts"]."txt";
	$_SESSION['specialcharslist'] = file_get_contents($entityrulesfile);
}
*/

$_SESSION['commonlist'] = $_POST['commonlist'];
$chunksize = (int)htmlspecialchars($_POST['chunksize']);
$chunknumber = (int)htmlspecialchars($_POST['chunknumber']);
$shiftsize = (int)htmlspecialchars($_POST['shiftsize']);
$lastprop = (int)htmlspecialchars($_POST['lastprop']);
$_SESSION['chunksize'] = $chunksize;
$_SESSION['chunknumber'] = $chunknumber;
$_SESSION['shiftsize'] = $chunksize - $shiftsize;
$_SESSION['lastprop'] = $lastprop / 100;
$_SESSION['chunkoption'] = $_POST['chunkoption'];

$fn = (isset($_SERVER['HTTP_X_FILENAME']) ? $_SERVER['HTTP_X_FILENAME'] : false);
$uploaded_files = array();

if (!is_dir('sessions/' . session_id())) {
	mkdir('sessions/' . session_id());
}
if (!is_dir('sessions/' . session_id() . '/uploads')) {
	mkdir('sessions/' . session_id() . '/uploads');
}
$directory = 'sessions/' . session_id() . '/uploads/';

if ($fn) {

	// AJAX call
	file_put_contents(
		$directory . $fn,
		file_get_contents('php://input')
	);
	$_SESSION['uploaded_files'][] = $fn;
	echo "$fn uploaded";
	exit();

}
else {

	// form submit
	$files = $_FILES['fileselect'];

	foreach ($files['error'] as $id => $err) {
		if ($err == UPLOAD_ERR_OK) {
			$fn = $files['name'][$id];
			move_uploaded_file(
				$files['tmp_name'][$id],
				$directory . $fn
			);
			$_SESSION['uploaded_files'][] = $fn;
			//echo "<p>File $fn uploaded.</p>";
		}
	}

}

header("Location: index.php");