<?php
# Ligature 1.0: http://www.desiquintans.com/ligature
# Ligature is free under version 2 or later of the GPL.
# This program is distributed with cursory support, but without
# warranty or guarantee of any sort.

header('Content-type: application/xml');
require ('admin/control.php');

$site = 'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['REQUEST_URI']);
if(substr($site, -1) != '/') {
	$site .= '/';
}
echo '<?xml version="1.0" encoding="ISO-8859-1" ?>';
           
    $getoffset = mysql_query("SELECT TZoffset, FeedNum FROM ".CONFIG_TBL." LIMIT 1");
    $offset = mysql_fetch_array($getoffset);
    $getid = mysql_query("SELECT Timestamp, Noun, Body FROM ".STORIES_TBL." ORDER BY Timestamp DESC LIMIT ".$offset['FeedNum']);
    ?>
    
    <rss version="2.0">
        <channel>
            <language>en-us</language>
            <title><?php echo $sitename_is; ?></title>
            <description><?php echo $sitedesc_is; ?></description>
            <link><?php echo $site; ?></link>
            <copyright>Copyright <?php echo $author_is.', '.date('Y'); ?></copyright>
    				<docs>http://blogs.law.harvard.edu/tech/rss</docs>
    				<generator>http://www.desiquintans.com/ligature</generator>
                <?php
                while ($row = mysql_fetch_array($getid)) {
                    $full = strip_tags($row['Body'], '<a>');
                    $body = substr($full, 0, 500);
                    if(strlen($full) > 500) $body = $body.'&#8230;';
                    print '
                    <item>
                    <title>'.ucfirst($row['Noun']).'</title>
                    <pubDate>'.date('D, j M Y H:i:s', $row['Timestamp']).$offset['TZoffset'].'</pubDate>
                    <author>'.$author_is.'</author>
                    <description>'.$body.'</description>
                    <link>'.$site.'index.php?noun='.$row['Noun'].'</link>
                    <guid isPermaLink="true">'.$site.'index.php?noun='.$row['Noun'].'</guid>
                    </item>
                    ';
                }
                ?>
        </channel>
    </rss>