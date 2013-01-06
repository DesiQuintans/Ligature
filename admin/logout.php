<?php
# Ligature 1.5: http://www.desiquintans.com/ligature
# Ligature is free under version 2 or later of the GPL.
# This program is distributed with cursory support, but without
# warranty or guarantee of any sort.

require('control.php');
require ('include/authenticate.php');

$expiry = time()-7200;
$wipe_id = setcookie('auth_id', '', $expiry, '', '', 0);
$wipe_user = setcookie('auth_username', '', $expiry, '', '', 0);
$wipe_hash = setcookie('auth_hash', '', $expiry, '', '', 0);

$pagetitle = 'Log out';
include ('include/head.htm');

if($wipe_id and $wipe_user and $wipe_hash) {
    echo '<p align="center"><img src="include/img/unauthorised.png" width="16" height="16" alt="" /> You are now logged out. Want to <a href="login.php">log in</a> again?</p>';
} else {
    echo '<p align="center"><img src="include/img/failure.png" width="16" height="16" alt="" /> You couldn\'t be logged out. Were you <a href="login.php">logged in</a> to begin with?</p>';
}

include ('include/foot.htm');
?>