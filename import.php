<?php session_start(); 
$page_title = "Import Data";
include("header.php");
?>

<div id="content">
<!--title-->
<h1>Cohort Convert - Import Data</h1>
<!-- navigation system -->
<?php include("navigation.php"); ?>

<!--description -->
<div id="description">
To use, select a dataset. If smoothed data has been calculated, one or more output results will appear in the list. Select a list member to rotate the data.
</div>
<div id="form">
	<form action="get_import.php" method="post" enctype="multipart/form-data">
	  <table>
        <tr>
          <th>Import file</th>
          <th>Format</th>
          <th>Data Ranges</th>
        </tr>
        <tr>
          <td><!--list of current datasets-->
          File to Import
            <input type="file" name="input_file" id="input_file" tabindex="1" /></td>
          <td><!--curve-fitting options-->
              <input type="radio" name="import_options" value="xls" checked="checked" />
            Excel (.xls)<br />
              <input type="radio" name="import_options" value="csv" />
            Excel (.csv)<br />
            <input type="radio" name="imports_options" value="xml" />XML<br />          </td>
          <td>Decimal Precision:
            <select name="decimals">
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
          <td><input type="submit" name="sub" value="Import Data" /></td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
      </table>
    
    </form>   

</div>

</div>
<?php
	include("footer.php");
	?>
