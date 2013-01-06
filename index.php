<?php
# Ligature 1.5: http://www.desiquintans.com/ligature
# Ligature is free under version 2 or later of the GPL.
# This program is distributed with cursory support, but without
# warranty or guarantee of any sort.

require ('admin/control.php');
require ('admin/include/autolinkhandler.php');

# This is a random link generator. It outputs a link leading to a random story, the link text being the first sentence of that story.
# The text is truncated to 75 characters and an ellipsis added if the sentence is long.
# This is at the top so it is always accessible with the {RANDOM} tag in the story.htm template.
	$getrandompage = mysql_query("SELECT Noun, Body FROM ".STORIES_TBL." ORDER BY RAND() LIMIT 1");
	$randompage = mysql_fetch_array($getrandompage);
	$firstsentence = strpos($randompage['Body'], '.');
	if($firstsentence > 75) $linktext = substr($randompage['Body'], 0, 75).'&#8230;'; else $linktext = substr($randompage['Body'], 0, $firstsentence+1);
	$randomlinktext = '<a href="index.php?noun='.$randompage['Noun'].'">'.$linktext.'</a>';

# This is a Next/Prev link generator. It outputs the relative URL to the story before or after the current one.
# If the current story is the newest or oldest, it makes no link but outputs something else. The actual output code can
# be configured in admin/control.php.
# Parameters: STATE (Next/Prev), STORYID (StoryID of current story).
function make_nav_links($state, $storyid, $nextstory_is, $neweststory_is, $prevstory_is, $oldeststory_is) {
	if($state == 'prev') {
		$resource = mysql_query("SELECT Noun FROM ".STORIES_TBL." WHERE StoryID < ".$storyid." ORDER BY StoryID DESC LIMIT 1");
    	$not_orphaned = mysql_fetch_array($resource);
	} elseif ($state = 'next') {
		$resource = mysql_query("SELECT Noun FROM ".STORIES_TBL." WHERE StoryID > ".$storyid." ORDER BY StoryID ASC LIMIT 1");
    	$not_orphaned = mysql_fetch_array($resource);
	}
	
    // 'Orphaned' meaning a story that is either the oldest or newest; not sandwiched between other stories.
    if($state == 'next') {
	    if($not_orphaned) {
	    	$linkurl = str_replace('{URL}', 'index.php?noun='.$not_orphaned['Noun'], $nextstory_is);
	    } else {
	    	$linkurl = $neweststory_is;
	    }
	} elseif ($state = 'prev') {
	    if($not_orphaned) {
	    	$linkurl = str_replace('{URL}', 'index.php?noun='.$not_orphaned['Noun'], $prevstory_is);
	    } else {
	    	$linkurl = $oldeststory_is;
	    }
	}
	return $linkurl;
}


if($_GET['noun']) { // Shows a specific noun's page.
	// Does this particular noun have a page?
	$check = strpos($_GET['noun'], '+');
	$check .= strpos($_GET['noun'], '%20');
	$check .= strpos($_GET['noun'], '*');
	if(!$check) { // $check finds suspicious characters commonly used for SQL injection (two types of spaces and an asterisk), and gives a 404 if any are found.
	$showpage = mysql_query("SELECT StoryID, Noun, Timestamp, Body FROM ".STORIES_TBL." WHERE Noun='".$_GET['noun']."'");
	}
	if(mysql_num_rows($showpage) == 0 or $check) {
	$pagetitle = '404 Page Not Found';
	include ('template/header.htm');
	include ('template/404.htm');
	include ('template/footer.htm');
	exit();
	}
	// If the script is still running, show the noun page.
	$row = mysql_fetch_array($showpage);
	setcookie('visited_ligature_before', TRUE, time()+60*60*24*30, '', '', 0);
	$pagetitle =& ucfirst($row['Noun']);
	$titlelc = $row['Noun'];
	include ('template/header.htm');
	$date = date($datepattern_is, $row['Timestamp']);
	$body = generate_linked_text($row['Noun'], $row['Body']);
	
	$use_template = array('{NUMBER}' => $row['StoryID'], '{TITLE}' => $pagetitle, '{BODY}' => $body, '{DATE}' => $date, '{NEXT}' => make_nav_links('next', $row['StoryID'], $nextstory_is, $neweststory_is, $prevstory_is, $oldeststory_is), '{PREV}' => make_nav_links('prev', $row['StoryID'], $nextstory_is, $neweststory_is, $prevstory_is, $oldeststory_is), '{TITLE_LC}' => $titlelc, '{RANDOM}' => $randomlinktext);
	if ($file = file_get_contents('template/story.htm')) {
	foreach($use_template as $key => $value) {
	    $file = @str_replace($key, $value, $file);
	}
	echo $file;
	}
	
	include ('template/footer.htm');
	exit();
}

if(isset($_GET['random'])) { // Show a random page.
	$showpage = mysql_query("SELECT StoryID, Noun, Timestamp, Body FROM ".STORIES_TBL." ORDER BY RAND() LIMIT 1");
	$row = mysql_fetch_array($showpage);
	setcookie('visited_ligature_before', TRUE, time()+60*60*24*30, '', '', 0);
	$pagetitle =& ucfirst($row['Noun']);
	$titlelc = $row['Noun'];
	include ('template/header.htm');
	$date = date($datepattern_is, $row['Timestamp']);
	$body = generate_linked_text($row['Noun'], $row['Body']);
	$use_template = array('{NUMBER}' => $row['StoryID'], '{TITLE}' => $pagetitle, '{BODY}' => $body, '{DATE}' => $date, '{NEXT}' => make_nav_links('next', $row['StoryID'], $nextstory_is, $neweststory_is, $prevstory_is, $oldeststory_is), '{PREV}' => make_nav_links('prev', $row['StoryID'], $nextstory_is, $neweststory_is, $prevstory_is, $oldeststory_is), '{TITLE_LC}' => $titlelc, '{RANDOM}' => $randomlinktext);
	if ($file = file_get_contents('template/story.htm')) {
	foreach($use_template as $key => $value) {
	    $file = @str_replace($key, $value, $file);
	}
	echo $file;
	}
	
	include ('template/footer.htm');
	exit();
}

// Default behaviour: show the most recent story.
// Determines what page should appear by default.
if(!empty($startingstory_is)) { // The user has defined a starting story.
	if(isset($_COOKIE['visited_ligature_before'])) { // Old reader. Show the newest story.
		$showpage = mysql_query("SELECT StoryID, Noun, Timestamp, Body FROM ".STORIES_TBL." ORDER BY Timestamp DESC LIMIT 1");
	} else { // New reader. Show the starting story.
		$showpage = mysql_query("SELECT StoryID, Noun, Timestamp, Body FROM ".STORIES_TBL." WHERE Noun='$startingstory_is'");
		setcookie('visited_ligature_before', 'TRUE', time()+60*60*24*30, '', '', 0);
	}
} else {
	$showpage = mysql_query("SELECT StoryID, Noun, Timestamp, Body FROM ".STORIES_TBL." ORDER BY Timestamp DESC LIMIT 1");
}
$row = mysql_fetch_array($showpage);
$pagetitle =& $defaulttitle_is;
$titlelc = $row['Noun'];
include ('template/header.htm');
$date = date($datepattern_is, $row['Timestamp']);
$body = generate_linked_text($row['Noun'], $row['Body']);
$use_template = array('{NUMBER}' => $row['StoryID'], '{TITLE}' => ucfirst($row['Noun']), '{BODY}' => $body, '{DATE}' => $date, '{NEXT}' => make_nav_links('next', $row['StoryID'], $nextstory_is, $neweststory_is, $prevstory_is, $oldeststory_is), '{PREV}' => make_nav_links('prev', $row['StoryID'], $nextstory_is, $neweststory_is, $prevstory_is, $oldeststory_is), '{TITLE_LC}' => $titlelc, '{RANDOM}' => $randomlinktext);
if ($file = file_get_contents('template/story.htm')) {
foreach($use_template as $key => $value) {
    $file = @str_replace($key, $value, $file);
}
echo $file;
}

include ('template/footer.htm');
exit();
?>