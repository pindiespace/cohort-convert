<?php session_start(); 
$page_title = "Input a Dataset";
include("header.php");
?>

<div id="content">
<!--title-->
<h1>Cohort Convert - Input data for a particular dataset</h1>
<!-- navigation system -->
<?php include("navigation.php"); ?>

<!--description -->
<div id="description">
Input data for a particular dataset.
<div id="form">
	<form method="post" action="get_curvefits.php">
    <table>
    	<tr>
    		<td>
<!--list of current datasets-->
			<div id="list_datasets">
			<select name="datasets">
    			<?php include("get_datasets.php"); ?>
    		</select>
    		</div>		    
            </td>
    		<td><input type="submit" name="sub" value="Select Dataset" />		
              </td>
		</tr>
    	<tr>
            <td>&nbsp;</td>
			<td>&nbsp;</td>
    	</tr>
	</table>
	 </form>   
</div>


</div>

</div>
<?php
	include("footer.php");
	?>
