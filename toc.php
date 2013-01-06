<?php
# Ligature 1.5: http://www.desiquintans.com/ligature
# Ligature is free under version 2 or later of the GPL.
# This program is distributed with cursory support, but without
# warranty or guarantee of any sort.

require ('admin/control.php');


$pagetitle = 'Table of Contents';

if(isset($_GET['bydate'])) {$sqlquery = "Timestamp DESC";} else {$sqlquery = "Noun ASC";}

$nouns = '';
$rawdump = mysql_query("SELECT Noun FROM ".STORIES_TBL." ORDER BY $sqlquery");
while ($stories = mysql_fetch_array($rawdump)) {
    $nouns .= '<li><a href="index.php?noun='.$stories['Noun'].'">'.ucfirst($stories['Noun']).'</a></li>';
}

include ('template/header.htm');

$use_template = array('{TITLE}' => $pagetitle, '{NOUNS}' => $nouns);
if ($file = file_get_contents('template/toc.htm')) {
    foreach($use_template as $key => $value) {
        $file = @str_replace($key, $value, $file);
    }
    echo $file;
}

include ('template/footer.htm');
?>