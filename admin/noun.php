<?php
# Ligature 1.5: http://www.desiquintans.com/ligature
# Ligature is free under version 2 or later of the GPL.
# This program is distributed with cursory support, but without
# warranty or guarantee of any sort.


require ('control.php');
require ('include/authenticate.php');
require ('include/autolinkhandler.php');

if($_GET['delete']) { // I have decided to disallow editing of nouns, so deleting and then creating a new one is the only way to change a noun.
	// Remove the custom noun from Existing lists, Pending list, Nouns list, and delete its story, if applicable.
        $storiestoupdate = mysql_query("SELECT Noun, Existing FROM ".STORIES_TBL." WHERE Existing LIKE '%".$_GET['delete']."%'");
        if(mysql_num_rows($storiestoupdate) != 0) {
        	while($story = mysql_fetch_array($storiestoupdate)) {
        		$oldexisting = str_ireplace($_GET['delete'].'|', '', $story['Existing']);
        		$updatestories = mysql_query("UPDATE ".STORIES_TBL." SET Existing='$oldexisting' WHERE Noun='".$story['Noun']."'");
        	}
        }
    $deleted = mysql_query("DELETE FROM ".PENDING_TBL." WHERE Noun='".$_GET['delete']."'");
    $deleted = mysql_query("DELETE FROM ".STORIES_TBL." WHERE Noun='".$_GET['delete']."'");
	$deleted = mysql_query("DELETE FROM ".NOUNS_TBL." WHERE Noun='".$_GET['delete']."'");
	# Confirm text at bottom
}

if($_POST['submit']) { // Add a new noun to the database.
	## Parse the input for any oddness.
	if (empty($_POST[noun])) $check = 1; else $noun = strip_tags($_POST['noun']);
	$check = strstr($noun, ' ');
	$check .= strstr($noun, '+');
	$check .= strstr($noun, '%20');
	$check .= strstr($noun, '&');
	$check .= strstr($noun, '?');
	$check .= strstr($noun, '*');
	$check .= strstr($noun, '<');
	if($check) {
		$pagetitle = 'Invalid noun';
		include ('include/head.htm');
		echo '<p align="center"><img src="include/img/failure.png" width="16" height="16" alt="" /> <strong>You submitted an invalid word.</strong>
		<br />The <a href="http://www.desiquintans.com/ligature-sts/manual/index.php?page=nounguide">noun guide</a> can tell you what characters are not allowed.
		<br />Please go back and redo the Noun field.</p>';
		include('include/foot.htm');
		exit();
	}
	// If the script has not exited, add the noun to the database.
        // Add this noun to the Existing list of all stories that might use it. Use a rough search, doesn't matter if we get false matches here.
        $storiestoupdate = mysql_query("SELECT Noun, Existing FROM ".STORIES_TBL." WHERE Body LIKE '%$noun%'");
        if(mysql_num_rows($storiestoupdate) != 0) {
        	while($story = mysql_fetch_array($storiestoupdate)) {
        		$newexisting = $story['Existing'].$noun.'|';
        		$updatestories = mysql_query("UPDATE ".STORIES_TBL." SET Existing='$newexisting' WHERE Noun='".$story['Noun']."'");
        	}
        }
		$makenew = mysql_query("INSERT INTO ".PENDING_TBL." (NounID, Noun) VALUES (NULL,'$noun')");
        $makenew = mysql_query("INSERT INTO ".NOUNS_TBL." (NounID, Noun) VALUES (NULL,'$noun')");
		# Confirm text at bottom.
}
$pagetitle = 'Manage custom nouns';
include ('include/head.htm');
if($deleted) {
	print '<p align="center"><img src="include/img/success.png" width="16" height="16" alt="" /> <strong>Noun deleted.</strong>';
} elseif($makenew) {
    echo '<p align="center"><img src="include/img/success.png" width="16" height="16" alt="" /> <strong>Noun added.</strong>';
}


echo '
	<script type="text/javascript">
	function formfocus() {
	  document.getElementById(\'noun\').focus();
	}
	window.onload = formfocus;
	</script>
<form method="post" action="'.$_SERVER['PHP_SELF'].'">
<fieldset style="margin-left:auto; margin-right:auto;">
<legend>Add a new noun?</legend>
<input type="text" id="noun" name="noun" size="30" value="" />&nbsp;<input type="submit" name="submit" value="Add noun" />
</fieldset>
</form>
';
$dumpall = mysql_query("SELECT Noun FROM ".NOUNS_TBL." ORDER BY Noun DESC");
echo '
<table border="0" cellpadding="5" cellspacing="2">
<tr bgcolor="#FFFFFF">
<th>Delete</th><th>Noun</th>
</tr>';
$bg = '#EEEEEE';
while($allnouns = mysql_fetch_array($dumpall)) {
	$bg = ($bg == '#FFFFFF' ? '#EEEEEE' : '#FFFFFF');
	print '<tr bgcolor="'.$bg.'"><td align="center">';
	print '<a href="noun.php?delete='.$allnouns['Noun'].'"><img src="include/img/failure.png" width="16" height="16" alt="Delete?" /></a>';
	print '</td><td>'.$allnouns['Noun'].'</td></tr>';
}
echo '</table>';
include ('include/foot.htm');
exit();
?>
