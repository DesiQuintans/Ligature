<?php
# Ligature 1.0: http://www.desiquintans.com/ligature
# Ligature is free under version 2 or later of the GPL.
# This program is distributed with cursory support, but without
# warranty or guarantee of any sort.

// This page contains the function definitions for all autolink handlers. The code may get confusing, so this page is heavily commented.

##################### GATHER ALL NOUNS ###########################

// This is stage one of Ligature's noun handler. This page simply gathers the nouns from a given story
// and stores them in the story's 'Existing' field in the database. 

function gather_all_nouns($text) {
	$nounlist = file('include/nounlist.txt'); // Opens the nounlist as a giant array.
	$existing_dump = ''; // $existing_dump is the dump variable for all of the nouns that are found in $_POST['body'].
	$text = explode('(!)))', $text);
	
	$oddoreven = 0; // This counter keeps track of odd or even (i.e. included or excluded) array entries.
	foreach($text as $value) {
		if($oddoreven == 0) {
			foreach($nounlist as $noun) { // Use nounlist.txt to find nouns.
				$noun = rtrim($noun);
				$found = eregi('[^>=A-Za-z]'.$noun.'(<| |"|\'|:|;|\-|,|/|\\|]|\)|&|!|\?|\.|es|s|ed|d|ing)', $value);
				if ($found !== false) { // Bang-Equals-Equals operator is correct for this condition.
					$existing_dump .= trim($noun).'|';
				}
			}
			$oddoreven = 1;
		} else {
			$oddoreven = 0;
		}
	}
	
	$oddoreven = 0; // This counter keeps track of odd or even (i.e. included or excluded) array entries.
	$getcustomnouns = mysql_query("SELECT Noun FROM ".NOUNS_TBL); // Use custom nouns table to find nouns.
	while($customnouns = mysql_fetch_array($getcustomnouns)) {
		$needle = $customnouns['Noun'];
		if($oddoreven == 0) {
			$found = eregi('[^>=A-Za-z/]'.$needle.'(<| |"|\'|:|;|\-|,|/|\\|]|\)|&|!|\?|\.|es|s|ed|d|ing)', $text);
			if ($found !== false) { // Bang-Equals-Equals operator is correct for this condition.
				$existing_dump .= trim($needle).'|';
			}
			$oddoreven = 1;
		} else {
			$oddoreven = 0;
		}
	}

	return $existing_dump;
}

##################################################################

##################### FIND PENDING ###############################

// This function searches through a story's existing nouns and adds them to the pending list if there is no story page for them.

function find_pending($existing_dump) {
	$getmasterlist = mysql_query("SELECT Noun FROM ".STORIES_TBL);
	$pending_dump = array();
	if(!empty($existing_dump)) {
		$existing_array = explode('|', substr($existing_dump, 0, -1));
		$listofpages = '';
		
		while($masterlist = mysql_fetch_array($getmasterlist)) {
			$listofpages .= '|'.$masterlist['Noun'];
		} // For some reason, this only works properly when you take the SQL results from the resource and put them into a custom array.
		foreach($existing_array as $line) {
				$found = eregi($line, $listofpages);
				if($found === false) { // No such story for that noun. Add it to the dump array for DB entry later.
					$pending_dump[] = $line;
				}
		}
		
		// Now dump the pending nouns into the database, making sure they stay unique.
		$pending_dump = array_unique($pending_dump);
		foreach ($pending_dump as $id => $entry) {
			$checkunique = mysql_query("SELECT Noun FROM ".PENDING_TBL." WHERE Noun='$entry'");
			$isunique = mysql_fetch_array($checkunique);
			if($isunique['Noun'] == $entry) { // Entry is not unique, remove it from the array.
				unset($pending_dump[$id]);
			} else {
				$addtopending = mysql_query("INSERT INTO ".PENDING_TBL." (NounID, Noun) VALUES (NULL, '$entry')");
				if(!$addtopending) echo mysql_error();
			}
		}
	}
	return true;
}

##################################################################

##################### GENERATE LINKED TEXT #######################

// This function searches through a story's existing nouns and either makes links out of them, or adds them to the pending list.

function generate_linked_text($noun, $text) {
	// Create the links inside the current page.
	$getexistinglist = mysql_query("SELECT Existing FROM ".STORIES_TBL." WHERE Noun='$noun'");
	$existing = mysql_fetch_array($getexistinglist);
	if($existing['Existing'] == '') {  // No existing nouns in this story. Just return the original text (after removing the exclusion tag).
		$parsedtext = str_replace(' (!))) ', ' ', $text); // Reduce spaced excl. tag to single space.
		$dump = str_replace('(!)))', '', $parsedtext); // Reduce inline excl. tag to empty character.
	} else { // Create links to existing nouns.
		$nounlist = explode('|', substr($existing['Existing'], 0, -1));

		$getmasterlist = mysql_query("SELECT Noun FROM ".STORIES_TBL);
		$listofpages = '';
		while($masterlist = mysql_fetch_array($getmasterlist)) {
			$listofpages .= '|'.$masterlist['Noun'];
		} // This compiles a string full of all the nouns that have stories, called $listofpages.

		$text = explode('(!)))', $text); // Separates the entire text into array entries by the Exclusion tag. A new entry is made wherever the tag appears, so it conspires that excluded blocks of text appear only on odd-numbered array entries.
		$oddoreven = 0; // This counter keeps track of whether a block is odd or even. 0 signifies even. 1 signifies odd. The loop below switches it between these two states during runtime.
		
		foreach($text as $value) { // Iterate through text array
			if($oddoreven == 0) {
				foreach($nounlist as $line) {
					$found = eregi('\|?'.$line.'\|?', $listofpages);
					if($line == $noun) $found = false; // Disallow a page from making a link to itself.
					if($found !== false) {
						// This all had to be regex, or else it would catch fragments of words too. Searching for car would return both car and carpet.
						$editedtext = eregi_replace('([^>=A-Za-z])('.$line.')(es|s|ed|d|ing)?([^(">)A-Za-z])', ' <a href="index.php?noun='.$line.'">\2\3</a>\4', isset($editedtext) ? $editedtext: $value);
					} else {
						$editedtext = isset($editedtext) ? $editedtext: $value;
					}
				}
				$dump[] = $editedtext;
				unset($editedtext);
				$oddoreven = 1;
			} else {
				$dump[] = $value;
				$oddoreven = 0;
			}
		}
		$dump = implode($dump);
	}
	$dump = preg_replace('/\\s{2,}/',' ',$dump); // This removes any multiple spaces that may have been left by the excl. tag removal or the implosion.
	return $dump;
}

##################################################################
?>