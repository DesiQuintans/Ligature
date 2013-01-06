<?php
# Ligature 1.5: http://www.desiquintans.com/ligature
# Ligature is free under version 2 or later of the GPL.
# This program is distributed with cursory support, but without
# warranty or guarantee of any sort.

require ('control.php');
require ('include/authenticate.php');

$pagetitle = 'Configure Ligature';
include ('include/head.htm');
include ('include/xmlfriendly.inc');

$getlatest = mysql_query("SELECT TZoffset, FeedNum FROM ".CONFIG_TBL." LIMIT 1");
$row = mysql_fetch_array($getlatest);

if($_POST[submit]) {
    $settings_changed = mysql_query("UPDATE ".CONFIG_TBL." SET DatePattern='".$_POST['datepattern']."', SiteUrl='".$_POST['siteurl']."', DefaultTitle='".$_POST['indextitle']."', Tagline='".$_POST['tagline']."', TZoffset='".$_POST['tzoffset']."', Author='".$_POST['author']."', SiteName='".$_POST['sitename']."', SiteDesc='".$_POST['sitedesc']."', FeedNum='".$_POST['feednum']."'");
    if ($settings_changed) {
        echo '<p align="center"><img src="include/img/success.png" width="16" height="16" alt="" /> Ligature\'s settings have been updated.</p>';
        include ('include/foot.htm');
        exit();
    }
}
?>

<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<fieldset>
<legend>Date and time display (<a href="http://www.desiquintans.com/ligature-sts/manual/index.php?page=formattingdates">[how?]</a>)</legend>
Date on stories:<input type="text" size="15" name="datepattern" value="<?php echo $datepattern_is; ?>" />
</fieldset>
<fieldset>
<legend>Site Settings</legend>
Address of index.php:<br />
<input type="text" size="40" name="siteurl" value="<?php echo $siteurl_is; ?>" />
<p>
Default greeting. Appears on front page only (e.g. <strong>Welcome</strong> -- mysite.com):<br />
<input type="text" size="40" name="indextitle" value="<?php echo $defaulttitle_is; ?>" />
</p><p>
Tagline (appears on every page: e.g. Welcome<strong> -- mysite.com</strong>):<br />
<input type="text" size="40" name="tagline" value="<?php echo $tagline_is; ?>" />
</p>
<input type="submit" name="submit" value="Update Preferences" />
</fieldset>

<fieldset><legend>Syndication/Metatag information</legend>
Name of your site:<input type="text" size="30" name="sitename" value="<?php echo $sitename_is; ?>" />
Name of author:<input type="text" size="20" name="author" value="<?php echo $author_is; ?>" />
<p>
Short description of project:<br />
<textarea cols="40" rows="5" name="sitedesc"><?php echo $sitedesc_is; ?></textarea>
<p>
GMT offset in hours:<input type="text" size="5" maxlength="5" name="tzoffset" value="<?php echo $row['TZoffset']; ?>" />
</p><p>
Number of new stories shown in feed:<input type="text" size="2" name="feednum" maxlength="2" value="<?php echo $row['FeedNum']; ?>" />
</p>
<input type="submit" name="submit" value="Update Preferences" />
</fieldset>
</form>

<?php
include ('include/foot.htm');
?>
