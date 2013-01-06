<?php
# Ligature 1.5: http://www.desiquintans.com/ligature
# Ligature is free under version 2 or later of the GPL.
# This program is distributed with cursory support, but without
# warranty or guarantee of any sort.


## Remove the double slashes from the start of the line below to disable all non-fatal error reporting.
//error_reporting(1);

## Set your mySQL details:
$DB_USER = 'username';          // Username
$DB_PASS = 'password';          // Password
$DB_HOST = 'localhost';         // mySQL hostname (usually 'localhost')
$DB_NAME = 'database';      // Name of database to store information in

## Name the mySQL tables Ligature will create and use (change only the second quoted value):
define('USERS_TBL', 'lig_users');        // User info
define('CONFIG_TBL', 'lig_config');      // Settings
define('STORIES_TBL', 'lig_stories');        // Stories
define('NOUNS_TBL', 'lig_nouns');        // User-added nouns
define('PENDING_TBL', 'lig_pending');        // Already-used nouns without pages for themselves

## What story should be displayed to users who have never visited before? Set this after you have written a story.
## Leave it blank ($startingstory_is = '';) if you want Ligature to always show the newest story.
$startingstory_is = 'beginning';

## This is the HTML for the Next Story link, and for what is shown when the current story is the newest.
## Use <a href="{URL}"> as the link code.
$nextstory_is = '<a href="{URL}">Next</a>';
$neweststory_is = 'This is the newest';

## This is the HTML for the Previous Story link, and for what is shown when the current story is the oldest.
## Use <a href="{URL}"> as the link code.
$prevstory_is = '<a href="{URL}">Prev</a>';
$oldeststory_is = 'This is the oldest';

## Don't edit below this line.

mysql_connect ($DB_HOST, $DB_USER, $DB_PASS) or die ('Couldn\'t connect to mySQL: '.mysql_error());
mysql_select_db ($DB_NAME) or die ('Couldn\'t select the database: '.mysql_error());

$settings = mysql_query("SELECT DefaultTitle, Tagline, SiteUrl, DatePattern, Author, SiteName, SiteDesc FROM ".CONFIG_TBL);
$ligconfig = @mysql_fetch_array($settings);
    $defaulttitle_is =& $ligconfig['DefaultTitle'];
    $tagline_is =& $ligconfig['Tagline'];
    $siteurl_is =& $ligconfig['SiteUrl'];
    $datepattern_is =& $ligconfig['DatePattern'];
    $author_is =& $ligconfig['Author'];
    $sitename_is =& $ligconfig['SiteName'];
    $sitedesc_is =& $ligconfig['SiteDesc'];

## DON'T REMOVE THE FOLLOWING UNSET(). This is important for security.
unset($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
?>
