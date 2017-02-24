<?php session_start(); 
$page_title = "Fit data to Age-Series vs. Year Curve";
include("header.php");
?>
<div id="content">
<!--title-->
<h1>Cohort Convert - Curve-Fitting of Input Data</h1>
<!-- navigation system -->
<?php include("navigation.php"); ?>

<!--description -->
<div id="description">
Select a dataset with some trait value plotted against years between 1880 and 2025. The software will converted the dataponts to smoothed curve, with approximated points for every year in the data range. Data will be output to a new matrix containing every birthyear
</div>
<div id="form">
	<form method="post" action="get_curvefits.php">
    <table>
    	<tr>
    	  <th>Dataset</th>
    	  <th>Year Interpolation</th>
    	  <th>Data Ranges</th>
  	  </tr>
    	<tr>
    		<td>
<!--list of current datasets-->
			<div id="list_datasets">
			<select name="datasets">
    			<?php include("get_datasets.php"); ?>
    		</select>
    		</div>   			 </td>
    		<td>
<!--curve-fitting options-->
			<input type="radio" name="curvefit_options" value="linear" checked="checked" />Linear (no smoothing)&nbsp;<br />
            <input type="radio" name="curvefit_options" value="nonlinear" />Nonlinear (smoothing)&nbsp;<br />    		</td>
		    <td>Decimal Precision: <select name="decimals">
              <option value="0">0(int)</option>
              <option value="1">1</option>
              <option value="2" selected="selected">2</option>
              <option value="3">3</option>
              <option value="4">4</option>
              <option value="5">5</option>
              <option value="6">6</option>
              <option
            </select></td>
    	</tr>
    	<tr>
            <td>&nbsp;</td>
			<td><input type="submit" name="sub" value="Fit the Curve" /></td>
    	    <td>&nbsp;</td>
    	</tr>
	</table>
    </form>   
</div>

</div>
<?php
	include("footer.php");
	?>