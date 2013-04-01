<?php

	$CardTable = "mtg_cards";
		
	function sqlBool($b, $db){
		$b = $db->real_escape_string($b);
		if ($b) return '1';
		else return '0';
	}

	function sqlString($s, $db){
		return trim($db->real_escape_string($s));
	}

	function DB_create_card($cardObject, $db){
		$name = $cardObject["name"];
		$mana_cost = ( isset($cardObject["mana_cost"]) ? $cardObject["mana_cost"] : null);
		$converted_mana_cost = ( isset($cardObject["converted_mana_cost"]) ? $cardObject["converted_mana_cost"] : null);
		$types = $cardObject["types"];
		$card_text = $cardObject["card_text"];
		$flavor_text = ( isset($cardObject["flavor_text"]) ? $cardObject["flavor_text"] : null );
		$power_toughness = ( isset($cardObject["power_toughness"]) ? $cardObject["power_toughness"] : null);
		$expansion = $cardObject["expansion"];
		$rarity = $cardObject["rarity"];
		$card_number = $cardObject["card_number"];
		$artist = $cardObject["artist"];
		return DB_insert_card($name, $mana_cost, $converted_mana_cost, $types, $card_text, $flavor_text, $power_toughness, $expansion, $rarity, $card_number, $artist, $db);
	}
	
	function DB_insert_card($name, $mana_cost, $converted_mana_cost, $types, $card_text, $flavor_text, $power_toughness, $expansion, $rarity, $card_number, $artist, $db){
		$result = $db->query( SQL_create_card($name, $mana_cost, $converted_mana_cost, $types, $card_text, $flavor_text, $power_toughness, $expansion, $rarity, $card_number, $artist) );
		if ($result){
			$id = $db->insert_id;
			return $id;
		}
		else {
			echo " [[ " . $db->error . " ]] ";
			return false;
		}
	}
	function SQL_create_card($name, $mana_cost, $converted_mana_cost, $types, $card_text, $flavor_text, $power_toughness, $expansion, $rarity, $card_number, $artist){
		
		global $CardTable;
		
		$card_text = addslashes($card_text);
		$flavor_text = addslashes($flavor_text);
		
		$SQL = "insert into $CardTable(name, mana_cost, converted_mana_cost, types, card_text, flavor_text, power_toughness, expansion, rarity, card_number, artist)
				values( '$name', '$mana_cost', '$converted_mana_cost', '$types', '$card_text', '$flavor_text', '$power_toughness', '$expansion', '$rarity', '$card_number', '$artist' );";
		//echo $SQL;
		//die();
		return $SQL;
	}
	
	function DB_card_exists($name, $db){
		$result = $db->query( SQL_card_exists($name, $db) );
		
		switch ($result->num_rows){
			case 0:
				return false;
				break;
			default:
				$fetchedCard = (array) $result->fetch_object();
				$fetchedCard["ID"] = "";
				$cardObject = array();
				foreach ($fetchedCard as $key => $value){
					if ($value){
						$cardObject[$key] = $value;
					}
				}
				return $cardObject;
		}
	}
	function SQL_card_exists($name, $db){
		global $CardTable;
		$name = sqlString($name, $db);
		return "Select * from $CardTable
				Where name = '$name'";
	}
?>