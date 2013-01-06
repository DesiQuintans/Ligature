<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="EN">
<head>
<!--
# Ligature 1.5: http://www.desiquintans.com/ligature
# Ligature is free under version 2 or later of the GPL.
# This program is distributed with cursory support, but without
# warranty or guarantee of any sort.
-->
<?php require ('admin/control.php'); ?>
<title>Ligature 1.5 Installation</title>
<style type="text/stylesheet"><![CDATA[
BODY {font-family: Verdana; font-size: 9pt; font-weight: normal; color: #000000; text-align: justify; margin-left: 50px;}
H1 {font-size: 15pt; font-weight: normal; text-align: center; text-decoration: underline; margin-left: -40px;}
H2 {font-size: 12pt; font-weight: normal; margin-left: -40px;}
fieldset {padding: 5px; width: 500px;}
]]></style>
</head>
<body>
<h1>Install <em>Ligature</em> 1.5</h1>
<?php
if($_POST[submit]) {
    // Part 1: Check entered information.
    if(!empty($_POST['user'])) $user =& $_POST['user']; else print "Enter a username.<br />";
    if(!empty($_POST['pass1'])) $pass1 =& $_POST['pass1']; else print "Enter a password.<br />";
    if(!empty($_POST['pass2'])) $pass2 =& $_POST['pass2']; else print "Must type password a second time.<br />";
    if(!empty($_POST['email'])) $email =& $_POST['email']; else print "Enter an email address.<br />";
    // Check if passwords matched.
    if($pass1 == $pass2) $pass =& $pass1; else print "<em>The two entered passwords do not match.</em><br />";

    if(!empty($_POST['siteurl'])) $siteurl =& $_POST['siteurl']; else print "Must give a blog URL.<br />";
    if(!empty($_POST['defaulttitle'])) $defaulttitle =& $_POST['defaulttitle']; else print"Enter an index.php title.<br />";
    if(!empty($_POST['tagline'])) $tagline =& $_POST['tagline']; else print "Enter a tagline.<br />";
    if(!empty($_POST['TZoffset'])) $tzoffset =& $_POST['TZoffset']; else print "Must enter a GMT difference.<br />";

    // If all fields are set, go ahead with installation, or else die.
    if($user && $pass && $email && $siteurl && $defaulttitle && $tagline && $tzoffset) {
        // Part 2: Create tables.

        $makePENDING = mysql_query("CREATE TABLE ".PENDING_TBL." (
        NounID INT NOT NULL AUTO_INCREMENT,
        Noun VARCHAR(50) NOT NULL,
        PRIMARY KEY (NounID),
        UNIQUE KEY (Noun)
        )");
        if($makePENDING) echo '<span style="color: green;">Table '.PENDING_TBL.' successfully created.</span><br />';
            else echo '<span style="color: #FF0000;">'.mysql_error().'</span> You can <a href="mailto:me@desiquintans.com">email the author</a> for help.<br />';

        $makeNOUNS = mysql_query("CREATE TABLE ".NOUNS_TBL." (
        NounID INT NOT NULL AUTO_INCREMENT,
        Noun VARCHAR(50) NOT NULL,
        PRIMARY KEY (NounID),
        UNIQUE KEY (Noun)
        )");
        if($makeNOUNS) echo '<span style="color: green;">Table '.NOUNS_TBL.' successfully created.</span><br />';
            else echo '<span style="color: #FF0000;">'.mysql_error().'</span> You can <a href="mailto:me@desiquintans.com">email the author</a> for help.<br />';

        if($makeNOUNS) $initialise_nouns = mysql_query("INSERT INTO ".NOUNS_TBL." (NounID, Noun) VALUES (NULL, 'Ligature')");
        if ($initialise_nouns) print '<span style="color: green;">'.NOUNS_TBL.' successfully initialised.</span><br />';
            else echo '<span style="color: #FF0000;">'.mysql_error().'</span> You can <a href="mailto:me@desiquintans.com">email the author</a> for help.<br />';

        $makeUSERS = mysql_query("CREATE TABLE ".USERS_TBL." (
        UserID INT NOT NULL AUTO_INCREMENT,
        UserName TINYTEXT NOT NULL,
        Password CHAR(32) NOT NULL,
        Email TINYTEXT NOT NULL,
        PRIMARY KEY (UserID),
        UNIQUE KEY (UserID)
        )");
        if($makeUSERS) echo '<span style="color: green;">Table '.USERS_TBL.' successfully created.</span><br />';
            else echo '<span style="color: #FF0000;">'.mysql_error().'</span> You can <a href="mailto:me@desiquintans.com">email the author</a> for help.<br />';

        if($makeUSERS) $initialise_users = mysql_query("INSERT INTO ".USERS_TBL." (UserID, UserName, Password, Email) VALUES (NULL, '$user', '".md5($pass)."', '$email')");
        if ($initialise_users) print '<span style="color: green;">'.USERS_TBL.' table is storing your login details.</span><br />';
            else echo '<span style="color: #FF0000;">'.mysql_error().'</span> You can <a href="mailto:me@desiquintans.com">email the author</a> for help.<br />';

        $makeSTORIES = mysql_query("CREATE TABLE ".STORIES_TBL." (
        StoryID INT NOT NULL AUTO_INCREMENT,
        Noun VARCHAR(50) NOT NULL,
        Existing LONGTEXT NOT NULL,
        Body LONGTEXT NOT NULL,
        Timestamp INT(20) NOT NULL,
        PRIMARY KEY (StoryID),
        UNIQUE KEY (Noun)
        )");
        if ($makeSTORIES) echo '<span style="color: green;">Table '.STORIES_TBL.' successfully created.</span><br />';
            else echo '<span style="color: #FF0000;">'.mysql_error().'</span> You can <a href="mailto:me@desiquintans.com">email the author</a> for help.<br />';

        $welcome = 'Please delete this post. It&#8217;s just here to welcome you to Ligature, to encourage you to explore the program and use its features in interesting and unheard-of ways, and to tell you that you can delete this post by going into the Manage Stories page in the Admin section.
        <p>
        Please refer your friends if you like the software. Just add
        </p><p>
        <pre>Published by &lt;a href="http://www.desiquintans.com/ligature">Ligature</a></pre>
        </p><p>
        somewhere in your footer&#8228;htm template. It&#8217;s there so that more people can discover Ligature and also so that I can Google for the link text and find your site, ask you about how you&#8217;re doing.
        </p><p>
        You can also use an image to link to my site, but please make the ALT text &#8220;Published by Ligature&#8221; so I can still Google for your site.
        </p>';
        $now = time();
        $makepost="INSERT INTO ".STORIES_TBL." (StoryID, Noun, Existing, Body, Timestamp)
        VALUES (NULL, 'Ligature', 'delete|feature|find|foot|friend|link|page|software|text|way|ligature|', '$welcome', '$now')";
        mysql_query ($makepost) OR die(mysql_error().'. Error at STORIES initialisation. You can <a href="mailto:desiq@bigpond.com">email the author</a> for help.');

        $makeCONFIG = mysql_query("CREATE TABLE ".CONFIG_TBL." (
        DefaultTitle TINYTEXT NOT NULL,
        Tagline TINYTEXT NOT NULL,
        SiteUrl TINYTEXT NOT NULL,
        DatePattern TINYTEXT NOT NULL,
        TZoffset VARCHAR(5) NOT NULL,
        Author TINYTEXT,
        SiteName TINYTEXT,
        SiteDesc TEXT,
        FeedNum TINYINT(2) NOT NULL
        )");
        if ($makeCONFIG) echo '<span style="color: green;">Table '.CONFIG_TBL.' successfully created.</span><br />';
            else echo '<span style="color: #FF0000;">'.mysql_error().'</span> You can <a href="mailto:me@desiquintans.com">email the author</a> for help.<br />';

        $setCONFIG="INSERT INTO ".CONFIG_TBL." (DefaultTitle, Tagline, SiteUrl, DatePattern, TZoffset, Author, SiteName, SiteDesc, FeedNum)
        VALUES ('".$_POST['defaulttitle']."', '".$_POST['tagline']."', '".$_POST['siteurl']."', 'jS \o\f F, Y', '$tzoffset', '$disp', '".$_POST['defaulttitle']."', 'A website about stories.', 4)";
        mysql_query ($setCONFIG) OR print (mysql_error() . '. Error at initialising CONFIG table.');
        if ($setCONFIG) echo '<span style="color: green;">Table '.CONFIG_TBL.' successfully initialised.</span><br />';
            else echo '<span style="color: #FF0000;">'.mysql_error().'</span> You can <a href="mailto:me@desiquintans.com">email the author</a> for help.<br />';

        die('<em>Ligature</em> was successfully installed. You can enter the <a href="admin/index.php">Admin section</a>
        and log in with the details you entered.
        <p>It is very important that you <strong>delete install.php</strong> from your server for security reasons.</p>');
    } else print '<strong>You must complete all form fields to install <em>Ligature</em>.</strong></p>';
}
?>

<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<fieldset>
<legend>Administrator information</legend>
User name:<br />
<input type="text" name="user" size="50" value="<?php if(isset($_POST['user'])) echo $_POST['user']; ?>" /><br />
Password:<br />
<input type="password" name="pass1" size="50" /><br />
Verify password:<br />
<input type="password" name="pass2" size="50" /><br />
Email address:<br />
<input type="text" name="email" size="50" value="<?php if(isset($_POST['email'])) echo $_POST['email']; ?>" /><br />
</fieldset>
<fieldset>
<legend>Site information</legend>
URL of Ligature&#8217;s index.php page:<br />
<input type="text" name="siteurl" size="50" value="<?php if(isset($_POST['siteurl'])) echo $_POST['siteurl']; ?>" /><br />
Title of front page (applies only to index.php page):<br />
<input type="text" name="defaulttitle" size="50" value="<?php if(isset($_POST['defaulttitle'])) echo $_POST['defaulttitle']; ?>" /><br />
Tagline (appears in every page&#8217;s titlebar):<br />
<input type="text" name="tagline" size="50" value="<?php if(isset($_POST['tagline'])) echo $_POST['tagline']; ?>" /><br />
Difference to GMT in hours:<br />
<input type="text" name="TZoffset" size="5" maxlength="5" value="<?php echo date('O'); ?>" />
<p>
Make sure you have given control.php the correct mySQL login information before continuing the installation.
</p><p>
<input type="submit" name="submit" value="Install Ligature" />
</p>
</fieldset>
</form>
</body>
</html>
