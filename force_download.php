<?php 
//force a download for a given file on the server, even if the browser would 
//otherwise try to read it (e.g. a text file)

	$file = $_POST['file'];

//quick check to verify that the file exists
	if(!file_exists($file) ) die("File not found");

//force the download
//header("Content-Disposition: attachment; filename=\"" . basename($file) . "\""); //strips off extension
//header("Content-Type: application/octet-stream;"); //generic download of anything
	header("Content-Disposition: attachment; filename=\"" . $file . "\"");
	header("Content-Length: " . filesize($file));
	header("Content-Type: application/vnd.ms-excel");

	readfile($file); 
	
?>
	