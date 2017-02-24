<?php session_start(); 
$page_title = "Instructions and Documentation";
include("header.php");
?>

<div id="content">
<!--title-->
<h1>Cohort Convert - Documentation</h1>
<!-- navigation system -->
<?php include("navigation.php"); ?>

<!--description -->
<div id="description">
<h2>About Cohort Convert</h2>
	This application allows y=f(x) data to be re-plotted, where x = years and y=a particular property, like personal income. Different series in the plot are assumed to be 'age bands' - data grouped by the age of the responder.The application performs a 'rotation' of the data, and re-plots it so the series are now birthyears or cohort bundles(generations). Ini this way, effects based on cohort/generational changes may be estimated. Options include:
	  <ul>
	  <li>Smoothing data into a larger number of points along x (year) and y (value) axes</li>
		<li>Rotated so that age range series are converted to birthyear series</li>
	    <li>Plotting data with several chart formats</li>
	    <li>Import/Export of data to Excel</li>
    </ul>
  The application is converting data plotted value against year, where individual data series bundle a range of ages, (implying a range of birthyears). Using the program, you can generate fitted approx data points for every possible age, therefore, every possible birthyear. In the second step. the grid is rotated so that the data series are now birthyears plotted against age. In the final step, one can explore groupings of birthyears versus the trait being analyzed - evidence for generations.  

</div>
<div id="quickstart">
<h2>Quickstart</h2>
<ol>
<li>Download the Excel template</li>
<li>Enter data into the Excel template</li>
<li>Upload the Excel template - this will create a new dataset, and assign your data under the dataset</li>
<li>Smooth your raw data using the "curvefit" option. You will have a new output table with smoothed data</li>
<li>Calculate matrix diagonals using the "matrix diagonals" option</li>
<li>Re-plot your data in terms of generations using the "output bundles" option</li>
</ol>
</div>

</div>
<?php
	include("footer.php");
	?>
