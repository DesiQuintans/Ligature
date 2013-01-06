<?php
# Ligature 1.5: http://www.desiquintans.com/ligature
# Ligature is free under version 2 or later of the GPL.
# This program is distributed with cursory support, but without
# warranty or guarantee of any sort.

require ('control.php');
require ('include/authenticate.php');

if(isset($_POST['noun'])) {
	// Parse the input for any oddness.
	include ('include/xmlfriendly.inc');
	include ('include/autolinkhandler.php');
	if($_POST['autobreak']) $body = nl2br($_POST['body']); else $body = $_POST['body'];
	foreach($ItemArray as $key => $value) {
		$body=@str_replace($key, $value, $body);
	}
	// Autolinking
	$existing = gather_all_nouns($body);
	find_pending($existing);
	// End autolinking
	$timestamp = time();
	$not_pending = mysql_query("DELETE FROM ".PENDING_TBL." WHERE Noun='".$_POST['noun']."'"); //IMPORTANT! Drops the used noun from the Pending list to prevent duplicates etc.
	if(!$not_pending) echo mysql_error();
	$post_made = mysql_query("INSERT INTO ".STORIES_TBL." (StoryID, Noun, Body, Timestamp, Existing) VALUES (NULL, '".$_POST['noun']."', '$body', $timestamp, '$existing')");
	if($post_made && $not_pending) {
		$pagetitle = 'Done';
		include ('include/head.htm');
		print '<p align="center"><img src="include/img/success.png" width="16" height="16" alt="" /> <strong>New story published.</strong>
		<br />Do you want to <a href="../index.php?noun='.$_POST['noun'].'">view it</a>?</p>';
		include ('include/foot.htm');
		exit();
    } else echo mysql_error();
}

if($_GET['new']) { // Add a new story.
	// First, is the noun in use actually in the Pending list?
	$pending = mysql_query("SELECT Noun from ".PENDING_TBL." WHERE Noun='".$_GET['new']."'");
	if(mysql_num_rows($pending) != 1) { // User was probably trying to cheat and make a story for which there was no noun.
		$pagetitle = 'No such noun';
		include ('include/head.htm');
		print '<p align="center"><img src="include/img/failure.png" width="16" height="16" alt="" /> <strong>Your story could not be published, either because there are
		multiple such nouns by the same name, or you were trying to cheat.</strong>
		<br />Please press Back in your browser.</p>';
		include ('include/foot.htm');
		exit();
	} // Continue with script.

// Show the edit form and end the script, if editing a story.
	$pagetitle = "Write a new story for ".$_GET['new'];
	include('include/head.htm');
	?>
	<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
	<fieldset>
	<legend>Details of the post (plain-text only)</legend>
	Noun:<input type="text" size="60" name="noun" value="<?php echo $_GET['new']; ?>" readonly />
	</p><p>
	<input type="checkbox" style="margin-left: 2em;" name="delete" /><strong>Delete</strong> this post?
	<input type="submit" name="submit" value="Post this story" style="margin-left: 4em;" />
	</p>
	</fieldset>
	<fieldset>
	<legend>Text of story (HTML allowed)</legend>
	<script type="text/javascript">edToolbar();</script>
	<textarea id="canvas" cols="80" rows="20" name="body"></textarea>
	<script type="text/javascript">var edCanvas = document.getElementById('canvas');</script>
	<p>
	<?php include ('include/autobreak.inc'); ?>
	</p><p>
	<input type="submit" name="submit" value="Post this story" />
	</p>
	</fieldset>
	</form>
<?php
include ('include/foot.htm');
exit();
}

// Show all pending nouns in a grid, alphabetically.
$pagetitle = 'All pending nouns';
include ('include/head.htm');

$fetchpending = mysql_query('SELECT Noun FROM '.PENDING_TBL);
if(mysql_num_rows($fetchpending) == 0) { // No Pending nouns; must be a new installation.
	echo 'To start writing stories, go to the <a href="noun.php">Custom Noun</a> page and add a word. Your name, for example. Then come back to this page, click on the word you created, and get writing.';
} else { // Pending nouns found
	// Compile the Existing list into a giant string.
	$existing_list = '';
	$fetchexisting = mysql_query('SELECT Existing FROM '.STORIES_TBL);
	while($existing = mysql_fetch_array($fetchexisting)) {
		// Add each Existing list to a giant string.
		$existing_list .= $existing['Existing'];
	}
	
	// Fetch the Pending nouns.
	$pending_array = array();
	while($pending = mysql_fetch_array($fetchpending)) {
		// Search the Existing list for instances of each Pending word, and add to an array with the Key as the word and the Value as the number of times it appears.
		$noun = $pending['Noun'];
		$pending_array[$noun] = substr_count($existing_list, $noun.'|'); // Bar added so it only finds whole words.
	}
	arsort($pending_array, SORT_NUMERIC); // Sort it so that the most common nouns end up at the top.

	# Begin printing results.
	echo '<p><strong>Click on a noun to add a story about it.</strong> Nouns that you use most often are towards the top.</p><table border="0" cellpadding="5" cellspacing="2">';
	$bg = '#EEEEEE';
	$counter = 0;
	$flag_counter = 1;
	$tablecells = '';
	foreach($pending_array as $noun => $instances) {
		switch($flag_counter) {
			case 1:
				$flag = '<img src="include/img/flag_red.png" />';
				break;
			case 2:
				$flag = '<img src="include/img/flag_orange.png" />';
				break;
			case 3:
				$flag = '<img src="include/img/flag_yellow.png" />';
				break;
			default:
				$flag = '';
		}
		$tablecells .= '<td align="center"><a href="pending.php?new='.$noun.'" title="'.$instances.' instances">'.$flag.' '.$noun.'</a></td>';
		$flag_counter +=1;
		$counter +=1;
		if($counter == 8) { // For every seven table cells, start a new row.
			$bg = ($bg == '#FFFFFF' ? '#EEEEEE' : '#FFFFFF');
			echo '<tr bgcolor="'.$bg.'">'.$tablecells.'</tr>';
			$counter = 0;
			$tablecells = ''; // Values wiped, counter started again.
		}
	}
	$bg = ($bg == '#FFFFFF' ? '#EEEEEE' : '#FFFFFF');
	if(!empty($tablecells)) { // If there are table cells that were not used in the While loop, print them now.
		echo '<tr bgcolor="'.$bg.'">'.$tablecells.'</tr>';
	}
	echo '</table>';
}
include ('include/foot.htm');
exit();
?>