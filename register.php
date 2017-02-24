<?php session_start(); 
$page_title = "Register";
include("header.php");
?>

<div id="content">
<!--title-->
<h1>Cohort Convert - Register a new user</h1>
<!-- navigation system -->
<?php include("navigation.php"); ?>

<!--description -->
<div id="description">
Register as a new user.
</div>
<div id="registration">
	<form id="regform" method="post" action="get_registration.php">
		<table id="regtable">
    		<tr>
            	<td>Name:</td>
                <td><input type="text" name="name" id="name" value="" size="80" maxlength="100" /></td>
            </tr>
            <tr>
            	<td>Organization:</td>
                <td><input type="text" name="organization" id="organization" value="" size="80" maxlength="100" /></td>
            </tr>
            <tr>
            	<td>Email:</td>
                <td><input type="text" name="email" id="email" value="" size="80" maxlength="100" /></td>
            </tr>
            <tr>
            	<td>Requested Username:</td>
                <td><input type="text" name="username" id="username" value="" size="15" maxlength="20" /></td>
            </tr>
            <tr>
            	<td>Requested Password:</td>
                <td><input type="password" name="password" id="password" value="" size="15" maxlength="20" /></td>
            </tr>
             <tr>
            	<td>Repeat Password:</td>
                <td><input type="password" name="password2" id="password2" value="" size="15" maxlength="20" /></td>
            </tr>
            <tr>
            	<td>Click to Submit:</td>
                <td><input type="submit" name="sub" id="sub" value="CLICK HERE" /></td>
            </tr>
    	</table>
    </form>
</div>

</div>
<?php
	include("footer.php");
	?>
