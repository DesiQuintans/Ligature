<?php
# Ligature 1.5: http://www.desiquintans.com/ligature
# Ligature is free under version 2 or later of the GPL.
# This program is distributed with cursory support, but without
# warranty or guarantee of any sort.

require ('control.php');
require ('include/authenticate.php');

$pagetitle = '"'.$sitename_is.'" is trucking on with Ligature';
include ('include/head.htm');
?>
Welcome to Ligature auto-linking storyteller. Ligature takes a story and turns every noun into a link automatically. Each link leads to another story, relevant to the linked noun itself, with yet more links. In this way what we end up with is a web of interlinked stories, like a wiki except there is no need to manually specify where a link goes.
<p>
Hover over a button on the toolbar above to find out what it does.
</p>

<h2>Must-read documentation</h2>
The <a href="http://www.desiquintans.com/ligature-sts/manual/index.php">Ligature Online Manual</a> explains a lot of things that aren&#8217;t
explained within Ligature itself.
<p>
The <a href="http://www.desiquintans.com/miniblog.php?blog=ligature_dev">Ligature Dev Diary</a> tracks updates, including bug fixes, and is also available as <a href="http://www.desiquintans.com/rss.php?miniblog=ligature_dev">an RSS feed</a>.
</p>
<?php
include ('include/foot.htm');
?>
