<?php
function get_line_content($l){
	//cardtextbox divs represent seperate lines, which we'll represent with underscores
	$v = str_replace("</div><div class=\"cardtextbox\">", ' _ ', $l);

	//Replace all images of icons with their alt text
	$v = preg_replace("#\<img[ a-zA-Z0-9/\.\?=&;\"]+alt=\"([\w ]+)\"[ a-zA-Z0-9/\.\?=&;\"]+\>#", '{$1}', $v);
	
	//Replace colour icons with a shorter version {Blue} -> {U}
	// but only if we can find a brace in the text (this is a mild optimisation)
	if (strpos($v, '{') !== false){
		$v = str_replace('{Blue}', '{U}', $v);
		$v = str_replace('{Black}', '{B}', $v);
		$v = str_replace('{White}', '{W}', $v);
		$v = str_replace('{Green}', '{G}', $v);
		$v = str_replace('{Red}', '{R}', $v);
		$v = str_replace('{Variable Colorless}', '{X}', $v);
	}
	
	//Strip out remaining tags and destroy whitespace
	return trim(strip_tags($v));
}

function tidy_line($l){
	return str_replace("\r", ' ', $l);
}

function download_card($name_search){

	$card = new card(null);
	$name_search = urlencode($name_search);
	$url = "http://gatherer.wizards.com/Pages/Search/Default.aspx?name=+[$name_search]";
	$lines = download_page($url);
	
	//Loop through lines of the html until the line contains "</title>"
	for ($i = 2; strpos($lines[$i], '</title>') === false; $i++){}
	//Now $lines[$i-1] contains the page title.
	// If the page title has "Card Search" there may be a single matching card,
	// for example "Forest", or it may be multiple results
	if (strpos($lines[$i-1], 'Card Search') === false){
		//Just a normal card page: parse the download
		$card = add_data_from_lines($card, $lines);
		return $card;
	}
	else{
		//Look for the searchTermDisplay and the number of results in it
		// (Use the existing instance of $i to save time);
		for (true; strpos($lines[$i], 'searchTermDisplay">') === false; $i++){}
		$l = $lines[$i];
		
		//Number of results is in brackets on this line, like (7)
		$openBracket = strpos($l, '(') + 1;
		if ($openBracket === false){
			$card->set_error('The card search results look strange. Can not return a card.');
			return $card;
		}
		
		$numberOfResults = substr($l, $openBracket, strpos($l, ')') - $openBracket);
		if ($numberOfResults == 0){
			$card->set_error('No cards match that search.');
			return $card;
		}
		
		//More than zero results - let's try to find an exact card.
		$mvid = matching_multiverse_id($name_search, $lines);
		if ($mvid){
			$lines = download_page("http://gatherer.wizards.com/Pages/Card/Details.aspx?multiverseid=$mvid");
			$card = add_data_from_lines($card, $lines);
		}
		else{
			$card->set_error('Multiple options');
		}
		return $card;
	}
}

function matching_multiverse_id($name, $lines){
	//Loop through lines of the html (from 200)
	// until one contains a link to the exact card name (or end of document [minus 30 lines])
	for ($i = 200; strpos($lines[$i], ">$name</a>") === false && $i < count($lines) - 30; $i++){}
	
	$l = $lines[$i];
	$mvidStart = strpos($l, '?multiverseid=');
	//Was it found? (Otherwise, we hit the end of the document)
	if ($mvidStart === false){ return false; }
	
	$mvidStart += 14; // Where 6 is the length of ?multiverseid=
	$mvidEnd = strpos($l, '"', $mvidStart);
	$multiverseId = substr($l, $mvidStart, $mvidEnd - $mvidStart);
	return $multiverseId;
}

//Return an array of lines representing a web page
function download_page($url){
	$page = file_get_contents($url);
	return explode(PHP_EOL, $page);
}

function add_data_from_lines($card, $lines){

	//var_dump($lines);
	
	//The key we found on this iteration, and for which we seek a value on the next
	$flag = null;
	//Boolean - is that value entirely HTML?
	$htmlFlag = false;
	//Boolean - does this non-HTML line need tidying/sanitising?
	$untidy = false;
	
	$found_result = false;
	
	$i = -1;
	foreach($lines as $line) {
		$i++;
		
		//All junk before 300th and after 500th line.
		if ($i < 300){ continue; }
		elseif ($i > 500){ break; }
		
		//Get the line with no markup or space
		$line = trim(strip_tags($line));
		
		//If it still has content
		if ($line){
			//If we flagged this line up as having a value, put it in the object.
			if ($flag){
				if ($untidy){
					$card->set($flag, tidy_line($line));
				}
				else{
					$card->set($flag, $line);
				}
				
				//And clear the flag, ready for next.
				$flag = null;
				$untidy = false;
				$htmlFlag = false;
				//We have found a result by now.
				$found_result = true;
			}
			else{
				//Normally not an html value, so default to false.
				$htmlFlag = false;
				switch ($line){
					case 'Card Name:':
						$flag = 'name';
						break;
					case 'Mana Cost:':
						$flag = 'mana_cost';
						$htmlFlag = true;
						break;
					case 'Converted Mana Cost:':
						$flag = 'converted_mana_cost';
						break;
					case 'Types:':
						$flag='types';
						break;
					case 'Card Text:':
						//Plain text with interspersed icons (maybe), treat as HTML
						$flag = 'card_text';
						$htmlFlag = true;
						break;
					case 'Flavor Text:':
						$flag = 'flavor_text';
						$untidy = true;
						break;
					case 'Watermark:':
						$flag = 'watermark';
						break;
					case 'P/T:':
						$flag = 'power_toughness';
						break;
					case 'Expansion:':
						$flag = 'expansion';
						break;
					case 'Rarity:':
						$flag = 'rarity';
						break;
					case 'Card Number:':
						$flag = 'card_number';
						break;
					case 'Artist:':
						$flag = 'artist';
						break;
				}
			}
		
			//echo $line . "\n";
			//if ($flag) echo " >>>> KEY [$flag]\n";
		}
		elseif ($htmlFlag && $flag){
			//No plaintext content, but we are waiting for an html value:
			// Some value lines (like mana cost) are just tags, so we must regex for them.
			// They fall after an opening <div="value"> tag, so we skip that line (+1)
			$l = $lines[$i+1];
			
			$card->set($flag, get_line_content($l));
			//echo " >>>> VALUE HTML [$value] ----- \n";
			$flag=null;
			$htmlFlag=false;
		}
	}
	
	//Done parsing every line of the page.
	// Check if we found anything, otherwise it wasn't a card page.
	if (!$found_result){
		$card->set_error('No card with that name');
	}
	
	return $card;
}

?>