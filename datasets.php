<?php session_start(); 
$page_title = "Datasets";
include("header.php");
?>

<div id="content">
<!--title-->
  <h1>Cohort Convert - Create or Edit Datasets</h1>
<!-- navigation system -->
	<?php include("navigation.php"); ?>

<!--description -->
	<div id="description">
Use this area to define a dataset. A dataset has age as the independent variable, and some trait (e.g. household income) as the dependent variable. Once a dataset is defined, it can have its data opened and edited.
	</div>
    
<!--form allowing creation of a dataset-->
	<div id="form">

<!--list of current datasets-->
	<div id="list_datasets">
    <form method="post" action="edit_datasets.php">
    <table>
      <tr>
        <td>
		<select name="datasets">
    		<?php include("get_datasets.php"); ?>
    	</select>
      
        </td>
        <td>
        	<input type="radio" name="datasets_option" value="edit" />Edit<br />
            <input type="radio" name="datasets_option" value="delete" />Delete<br />
        </td>
        </tr>
        <td>&nbsp;
        
        </td>
        <td>
        <input type="submit" name="sub" value="Go" />
        </td>
      </tr>
    </table>
     </form>
   </div>
<!--form for making a new dataset-->
	<div id="dataset_form">
    <form method="post" action="scripts/php/create_datasets.php">
	<table width="200" border="1">
      <tr>
        <td colspan="2"><strong>New Dataset:</strong></td>
      </tr>
      <tr>
        <td><strong>Author:</strong></td>
        <td><input name="author" type="text" id="author" title="username" value="" size="20" maxlength="20"/></td>
      </tr>
      <tr>
        <td><strong>Title:</strong></td>
        <td><input name="title" type="text" id="title" title="username" value="" size="20" maxlength="20"/></td>
      </tr
        >
      <tr>
        <td><strong>Short Title:</strong></td>
        <td><input name="title_short" type="text" id="title_short" title="username" value="" size="20" maxlength="20"/></td>
      </tr
        >
	  <tr>
        <td><strong>Legend X Axis:</strong></td>
	    <td><input name="legend_x" type="text" id="legend_x" title="username" value="" size="20" maxlength="20"/></td>
      </tr>
	  <tr>
	    <td><strong>Legend Y Axis:</strong></td>
	    <td><input name="legend_y" type="text" id="legend_y" title="username" value="" size="20" maxlength="20"/></td>
      </tr>
	  <tr>
	    <td><strong>Generation Model:</strong></td>
	    <td><input name="generation" type="text" id="generation" title="username" value="" size="20" maxlength="20"/></td>
      </tr>
	  <tr>
	    <td>&nbsp;</td>
	    <td><input type="submit" name="sub" value="Create Dataset" /></td>
      </tr>
    </table>
    </form>
	</div>
</div>
<?php
	include("footer.php");
	?>
