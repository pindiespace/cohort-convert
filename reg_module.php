<div id="reg_module">
	<div id="form">
    	<form method="post" action="get_login.php">
	  <table width="200" border="1">
        <tr>
          <td colspan="2"><strong>Login:</strong></td>
        </tr>
        <tr>
          <td><strong>Username:</strong></td>
          <td><input type="text" name="username" value="" title="username" size="20" maxlength="20" 
          value="<?php if(isset($username)) echo $username; ?>" /></td>
        </tr>
        <tr>
          <td><strong>Password:</strong></td>
          <td><input type="password" name="password" value="" title="password" size="10" maxlength="10" /></td>
        </tr
        ><tr>
          <td><!--register if no user account available-->
    <span class="hotlink">
    Don't have an account? Register by clicking <a href="register.php">here</a>.
    </span></td>
          <td><input type="submit" name="sub" value="Login" /></td>
        </tr>
      </table>
      	</form>
	</div>

</div>    