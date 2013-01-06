<?php
# Ligature 1.5: http://www.desiquintans.com/ligature
# Ligature is free under version 2 or later of the GPL.
# This program is distributed with cursory support, but without
# warranty or guarantee of any sort.


require ('control.php');
require ('include/authenticate.php');

if($_POST['delete']) {  // Is a story being deleted?
	$deleted = mysql_query("DELETE FROM ".STORIES_TBL." WHERE Noun='".$_POST['Noun']."'");
	$made_pending = mysql_query("INSERT INTO ".PENDING_TBL." (NounID, Noun) VALUES (NULL, '".$_POST['Noun']."')");
	if($deleted && $made_pending) {
		$pagetitle = 'Done';
		include ('include/head.htm');
		print '<p align="center"><img src="include/img/success.png" width="16" height="16" alt="" /> <strong>Story deleted.</strong>
		<br />Do you want to <a href="pending.php">make a new one</a>?
		</p>';
		include ('include/foot.htm');
		exit();
	}
}

if($_POST['edit']) { // Existing story is being edited.
	// Parse the input for any oddness.
	include ('include/xmlfriendly.inc');
	include ('include/autolinkhandler.php');
	if($_POST['autobreak']) $body = nl2br($_POST['body']); else $body =& $_POST['body'];
	foreach($ItemArray as $key => $value) {
		$body = @str_replace($key, $value, $body);
	}
	// Autolinking
	$existing = gather_all_nouns($body);
	find_pending($existing);
	// End autolinking
	$edited = mysql_query("UPDATE ".STORIES_TBL." SET Body='$body', Existing='$existing' WHERE Noun='".$_POST['Noun']."'");
	if ($edited) {
		$pagetitle = 'Done';
		include ('include/head.htm');
		print '<p align="center"><img src="include/img/success.png" width="16" height="16" alt="" /> <strong>Story updated.</strong>
		<br />Do you want to <a href="../index.php?noun='.$_POST['Noun'].'">view it</a> or <a href="pending.php">make a new one</a>?</p>';
		include ('include/foot.htm');
		exit();
	} else { echo '<span style="color: #FF0000;">'.mysql_error().'</span>'; }
}

if($_GET['edit']) {// Show the edit form and end the script, if editing a story.
	$get_old = mysql_query("SELECT Noun, Body, Timestamp FROM ".STORIES_TBL." WHERE Noun='".$_GET['edit']."' LIMIT 1");
	$old = mysql_fetch_array($get_old);
	
	$pagetitle = 'Edit a story';
	include('include/head.htm');
	?>
	<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
	<fieldset>
	<legend>Details of the post (plain-text only)</legend>
	Noun:<input type="text" size="60" name="Noun" value="<?php echo $old['Noun']; ?>" readonly />
	</p><p>
	<input type="checkbox" style="margin-left: 2em;" name="delete" /><strong>Delete</strong> this post?
	<input type="submit" name="edit" value="Update this story" style="margin-left: 4em;" />
	</p>
	</fieldset>
	<fieldset>
	<legend>Text of story (HTML allowed)</legend>
	<script type="text/javascript">edToolbar();</script>
	<textarea id="canvas" cols="80" rows="20" name="body"><?php echo htmlspecialchars($old['Body']); ?></textarea>
	<script type="text/javascript">var edCanvas = document.getElementById('canvas');</script>
	<p>
	<?php include ('include/autobreak.inc'); ?>
	</p><p>
	<input type="submit" name="edit" value="Update this story" />
	</p>
	</fieldset>
	</form>
<?php
include ('include/foot.htm');
exit();
}

// Show all existing stories alphabetically.
$pagetitle = 'Manage stories';
include ('include/head.htm');
$dumpall = mysql_query("SELECT Noun FROM ".STORIES_TBL." ORDER BY Noun");
echo '
<table border="0" cellpadding="5" cellspacing="2">
<tr bgcolor="#FFFFFF">
<th>Click on a noun to edit its story.</th>
</tr>';
$bg = '#EEEEEE';
while($allnouns = mysql_fetch_array($dumpall)) {
	$bg = ($bg == '#FFFFFF' ? '#EEEEEE' : '#FFFFFF');
	echo '<tr bgcolor="'.$bg.'"><td align="center"><a href="story.php?edit='.$allnouns['Noun'].'">'.$allnouns['Noun'].'</td></tr>';
}
echo '</table>';
include ('include/foot.htm');
exit();
?>
