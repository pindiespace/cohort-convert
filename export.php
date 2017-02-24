<?php session_start(); 
$page_title = "Export Data";
include("header.php");
?>

<div id="content">
<!--title-->
<h1>Cohort Convert - Export Data in Excel or XML Format</h1>
<!-- navigation system -->
<?php include("navigation.php"); ?>

<!--description -->
<div id="description">
To use, select a dataset. Select a table from the second menu, then an output format, Finally, set decimal precision.
</div>
<div id="form">
	<form method="post" action="get_export.php">
	  <div id="form2">
        <table>
          <tr>
            <th>Datasets</th>
            <th>Table to Export</th>
            <th>Format</th>
            <th>Data Ranges</th>
          </tr>
          <tr>
            <td><!--list of current datasets-->
                <div id="list_datasets">
                  <select name="datasets">
                    <?php include("get_datasets.php"); ?>
                  </select>
              </div></td>
            <td><!--table in the dataset to export-->
              <select name="data_table">
                <option value="input">Input</option>
                <option value="input_curvefit">Input with Interpolated Years</option>
                <option value="input_diagonals">Input with Interpolated Birthyears</option>
                <option value="output_bundles" checked="checked">Output, Generations vs. Year</option>
              </select>
              <br />            </td>
            <td><input type="radio" name="export_options" value="xls" checked="checked" /> 
              Excel (.xls)
<br />
<input type="radio" name="export_options" value="csv" />
Excel (.csv)
<br />
<input type="radio" name="export_options" value="xml" />
 XML<br /></td>
            <td>Decimal Precision:
              <select name="decimals">
                  <option value="0">0(int)</option>
                  <option value="1">1</option>
                  <option value="2">2</option>
                  <option value="3">3</option>
                  <option value="4" selected="selected">4</option>
                  <option value="5">5</option>
                  <option value="6">6</option>
                  <option
            
            
              </select></td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td><input type="submit" name="sub" value="Export Table" /></td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
        </table>
      </div>
	</form>   

</div>

</div>
<?php
	include("footer.php");
	?>
