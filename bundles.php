<?php session_start(); 
$page_title = "Generational Plots";
include("header.php");
?>
<div id="content">
<!--title-->
<h1>Cohort Convert - Bundle Padded Age data to create generation series plot</h1>
<!-- navigation system -->
<?php include("navigation.php"); ?>

<!--description -->
<div id="description">
To use, select a dataset. If smoothed data has been calculated, one or more output results will appear in the list. Select a list member to rotate the data.
</div>
<div id="form">
	<form method="post" action="get_bundles.php">
	  <table>
        <tr>
          <th>Datasets</th>
          <th>Generation Bundling</th>
          <th>Generation Model</th>
          <th>Data Ranges</th>
        </tr>
        <tr>
          <td><!--list of current datasets-->
              <div id="list_datasets">
                <select name="datasets">
                  <?php include("get_datasets.php"); ?>
                </select>
            </div></td>
          <td><!--curve-fitting options-->
              <input type="radio" name="bundles_options" value="simple_average" checked="checked" />
            Simple Average<br />
              <input type="radio" name="bundles_options" value="weighted_average" />
            Weighted Average<br />          </td>
          <td>Generation:
          
            <select name="generations">
              <?php include("get_generations.php"); ?>
            </select>          
          
          </td>
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
          <td>&nbsp;</td>
          <td><input type="submit" name="sub" value="Plot Generations" /></td>
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
