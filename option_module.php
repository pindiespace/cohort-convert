<div id="options">
<ul>
<?php
//use the session ID to recover some user information
//list of options available at this point
//echo "<div id=\"options\">\n <ul>\n";
if(isset($_SESSION['ID'])) {
	
//find the datasets associated uniquely with this user

	if(isset($_SESSION['dataset_id'])) {
		echo "<li>Your current dataset ID is ".$_SESSION['dataset_id']." - you have the following options:</li>\n";
		echo "
		<ul>
		 <li><a href=\"curvefits.php\" title=\"smooth incoming data\">Smooth incoming data for further processing</a></li>\n
		 <li><a href=\"diagonals.php\" title=\"calculate diagonals\">Calculate matrix diagonals</a></li>\n
		 <li><a href=\"bundles.php\" title=\"calculate bundles\">Set and recalculate using generational models</a></li>\n
		</ul>";
		}
//we alreays write these
		echo "<li>Change Dataset</li>\n";
		echo "<ul>\n";
		echo "<li><a href=\"datasets.php\">Create or edit an existing Dataset</a></li>\n";
		echo "</ul>\n";
	}
else {
	echo "<li>No options available, untile you register or log in</li>\n";
	}

//echo "</ul>\n</div>\n";

?>
</ul>
</div>
