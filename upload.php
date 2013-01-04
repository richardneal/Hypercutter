<?php
/*
Server-side PHP file upload code for HTML5 File Drag & Drop demonstration
Featured on SitePoint.com
Developed by Craig Buckler (@craigbuckler) of OptimalWorks.net
*/
session_start();
$chunksize = (int)htmlspecialchars($_POST['chunksize']);
$shiftsize = (int)htmlspecialchars($_POST['shiftsize']);
$lastprop = (int)htmlspecialchars($_POST['lastprop']);
$_SESSION['chunksize'] = $chunksize;
$_SESSION['shiftsize'] = $chunksize - $shiftsize;
$_SESSION['lastprop'] = $lastprop / 100;
if(isset($_POST['apostrophes']) && $_POST['apostrophes'] == 'yes') {
	$_SESSION['apostrophes'] = 'yes';
} else {
	$_SESSION['apostrophes'] = 'no';
}
if(isset($_POST['hyphens']) && $_POST['hyphens'] == 'yes') {
	$_SESSION['hyphens'] = 'yes';
} else {
	$_SESSION['hyphens'] = 'no';
}

$fn = (isset($_SERVER['HTTP_X_FILENAME']) ? $_SERVER['HTTP_X_FILENAME'] : false);
$uploaded_files = array();

if ($fn) {

	// AJAX call
	file_put_contents(
		'uploads/' . $fn,
		file_get_contents('php://input')
	);
	$_SESSION['uploaded_files'][] = $fn;
	//echo "$fn uploaded";
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
				'uploads/' . $fn
			);
			$_SESSION['uploaded_files'][] = $fn;
			//echo "<p>File $fn uploaded.</p>";
		}
	}

}

header("Location: index.php");