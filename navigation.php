<?php

//put up a navigation toolbar
echo "<div id=\"navigation\"><span id=\"navformat\">\n
<ul>\n";
if(isset($_SESSION['ID'])) {
	echo "<li><span id=\"bolder\">logged in</span></li>\n";
	
	}
else {
	echo "<li><span id=\"bolder\">not logged in</span></li>\n";
	}
//make rest of navigation system
echo "<li><a href=\"index.php\">Home</a>\n";
if(isset($_SESSION['ID'])) {
	echo "<li><a href=\"get_logout.php\">Logout</a>\n
	<li><a href=\"import.php\">Import Data</a></li>\n
	<li><a href=\"input_datasets.php\">Input Data</a></li>\n
	<li><a href=\"export.php\">Export Data</a></li>\n
	<li><a href=\"datasets.php\">Create/Edit/Delete Datasets</a></li>\n";
	if(isset($_SESSION['dataset_id'])) {
		echo "<li><a href=\"curvefits.php\">Curve Fit</a></li>\n
		<li><a href=\"diagonals.php\">Matrix Diagonals</a></li>\n
		<li><a href=\"bundles.php\">Cohort Bundles</a></li>\n";
		}
	}
else {
	echo "<li><a href=\"register.php\">Register</a></li>\n";
	}

echo "</ul>\n
</span>\n
</div>\n";

?>