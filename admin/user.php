<?php
# Ligature 1.5: http://www.desiquintans.com/ligature
# Ligature is free under version 2 or later of the GPL.
# This program is distributed with cursory support, but without
# warranty or guarantee of any sort.

require ('control.php');
require ('include/authenticate.php');
include ('include/xmlfriendly.inc');

$pagetitle = 'Editing user';
include ('include/head.htm');

if ($_POST['submitted']) {
	$password = '';
	if(!empty($_POST['pass1'])) { // Is the user changing the password?
		if(empty($_POST['pass2'])) { // User forgot to fill out the second password field.
			print '<p align="center"><img src="include/img/failure.png" width="16" height="16" alt="" /> <strong>You need to fill out both password fields.</strong><br />';
			include ('include/foot.htm');
			exit();
		}
		$test_password = trim(ereg_replace( ' +', '', $_POST['pass1'])); // This removes all spaces from the password.
		if(($_POST['pass1'] == $_POST['pass2']) && !empty($test_password)) { // Both password fields match, and are not made exclusively of spaces.
			$password = ' Password="'.md5($_POST['pass1']).'",';
		} else {
			print '<p align="center"><img src="include/img/failure.png" width="16" height="16" alt="" /> <strong>The two entered passwords do not match.</strong><br />';
			include ('include/foot.htm');
			exit();
		}
	}
	$edited = mysql_query("UPDATE ".USERS_TBL." SET$password Email='".$_POST['email']."' WHERE UserID=".$_POST['UserID']);
	if ($edited) {
		$pagetitle = 'Done';
		print '<p align="center"><img src="include/img/success.png" width="16" height="16" alt="" /> <strong>User edited.</strong><br />';
		include ('include/foot.htm');
		exit();
	}
}

// Edit user
$get_user = mysql_query("SELECT DispName, Email, Bio FROM ".USERS_TBL." WHERE UserID=".$_COOKIE['auth_id']);
$user = mysql_fetch_array($get_user);
?>
	<script type="text/javascript">
	function formfocus() {
	  document.getElementById('password').focus();
	}
	window.onload = formfocus;
	</script>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="UserID" value="<?php echo $_COOKIE['auth_id']; ?>" />
    <fieldset>
    <legend>Administration details</legend>
    Email:<input type="text" name="email" size="20" value="<?php echo $user['Email']; ?>" />
    Password (if you want to change it):<input type="password" id="password" name="pass1" size="20" />
    Verify password:<input type="password" name="pass2" size="20" />
    <p>
    <input type="submit" name="submitted" value="Update user" />
    </p>
    </fieldset>
    </form>
<?php
exit();
?>
