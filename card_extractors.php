<?php
function get_line_content($l){
	//cardtextbox divs represent seperate lines, which we'll represent with underscores
	$v = str_replace("</div><div class=\"cardtextbox\">", " _ ", $l);
	
	//Replace all images of icons with their alt text
	$v = preg_replace("#\<img[ a-zA-Z0-9/\.\?=&;\"]+alt=\"([\w ]+)\"[ a-zA-Z0-9/\.\?=&;\"]+\>#", '{$1}', $v);
	
	//Strip out remaining tags and destroy whitespace
	return trim( strip_tags($v) );
}

function download_card($name_search){

	$name_search = urlencode($name_search);
	$url = "http://gatherer.wizards.com/Pages/Search/Default.aspx?name=+[$name_search]";
	$page = file_get_contents( $url );
	$lines = explode( PHP_EOL, $page );

	//The key we found on this iteration, and for which we seek a value on the next
	$flag = null;
	//Boolean - is that value entirely HTML?
	$htmlFlag = false;

	$found_result = false;
	
	$i = -1;
	foreach( $lines as $line ) {
		$i++;
		
		//All junk before 300 lines and after 500th line.
		if ($i < 300){ continue; } 
		elseif ($i > 500){ break; }
		
		//Get the line with no markup or space
		$line = trim( strip_tags($line) );
		
		//If it still has content
		if ($line){
			//If we flagged this line up as having a value, put it in the object.
			if ($flag){
				$cardObject[$flag] = $line;
				//And clear the flag, ready for next.
				$flag = null;
				$htmlFlag = false;
				//We have found a result by now.
				$found_result = true;
			}
			else{
				//Normally not an html value, so default to false.
				$htmlFlag = false;
				switch ($line){
					case "Card Name:":
						$flag = "name";
						break;
					case "Mana Cost:":
						$flag = "mana_cost";
						$htmlFlag = true;
						break;
					case "Converted Mana Cost:":
						$flag = "converted_mana_cost";
						break;
					case "Types:":
						$flag="types";
						break;
					case "Card Text:":
						//Plain text with interspersed icons (maybe), treat as HTML
						$flag = "card_text";
						$htmlFlag = true;
						break;
					case "Flavor Text:":
						$flag = "flavor_text";
						break;
					case "Watermark:":
						$flag = "watermark";
						break;
					case "P/T:":
						$flag = "power_toughness";
						break;
					case "Expansion:":
						$flag = "expansion";
						break;
					case "Rarity:":
						$flag = "rarity";
						break;
					case "Card Number:":
						$flag = "card_number";
						break;
					case "Artist:":
						$flag = "artist";
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
			
			$cardObject[$flag] = get_line_content($l);
			//echo " >>>> VALUE HTML [$value] ----- \n";
			$flag=null;
			$htmlFlag=false;
		}
	}
	
	//Done parsing every line of the page.
	// Check if we found anything, otherwise it wasn't a card page.
	if (!$found_result){
		$cardObject["error"] = "No card with that name";
	}
	
	return $cardObject;
}
?>