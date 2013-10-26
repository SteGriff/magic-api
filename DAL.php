<?php

	$CardTable = 'mtg_cards';

	function sqlString($s, $db){
		return trim($db->real_escape_string($s));
	}

	function DB_create_card($card, $db){
		$name = $card->get('name');
		$mana_cost = $card->get('mana_cost');
		$converted_mana_cost = $card->get('converted_mana_cost');
		$types = $card->get('types');
		$card_text = $card->get('card_text');
		$flavor_text = $card->get('flavor_text');
		$power_toughness = $card->get('power_toughness');
		$expansion = $card->get('expansion');
		$rarity = $card->get('rarity');
		$card_number = $card->get('card_number');
		$artist = $card->get('artist');
		return DB_insert_card($name, $mana_cost, $converted_mana_cost, $types, $card_text, $flavor_text, $power_toughness, $expansion, $rarity, $card_number, $artist, $db);
	}
	
	function DB_insert_card($name, $mana_cost, $converted_mana_cost, $types, $card_text, $flavor_text, $power_toughness, $expansion, $rarity, $card_number, $artist, $db){
		$result = $db->query( SQL_create_card($name, $mana_cost, $converted_mana_cost, $types, $card_text, $flavor_text, $power_toughness, $expansion, $rarity, $card_number, $artist, $db) );
		if ($result){
			$id = $db->insert_id;
			return $id;
		}
		else {
			echo " [[ {$db->error} ]] ";
			return false;
		}
	}
	function SQL_create_card($name, $mana_cost, $converted_mana_cost, $types, $card_text, $flavor_text, $power_toughness, $expansion, $rarity, $card_number, $artist, $db){
		
		global $CardTable;
		
		$name = sqlString($name, $db);
		$mana_cost = sqlString($mana_cost, $db);
		$converted_mana_cost = sqlString($converted_mana_cost, $db);
		$types = sqlString($types, $db);
		$card_text = sqlString($card_text, $db);
		$flavor_text = sqlString($flavor_text, $db);
		$power_toughness = sqlString($power_toughness, $db);
		$expansion = sqlString($expansion, $db);
		$rarity = sqlString($rarity, $db);
		$card_number = sqlString($card_number, $db);
		$artist = sqlString($artist, $db);

		$SQL = "insert into $CardTable(name, mana_cost, converted_mana_cost, types, card_text, flavor_text, power_toughness, expansion, rarity, card_number, artist)
				values( '$name', '$mana_cost', '$converted_mana_cost', '$types', '$card_text', '$flavor_text', '$power_toughness', '$expansion', '$rarity', '$card_number', '$artist' );";
		//echo $SQL;
		//die();
		return $SQL;
	}
	
	function DB_existing_card($name, $db){
		$result = $db->query( SQL_existing_card($name, $db) );
		
		switch ($result->num_rows){
			case 0:
				return null;
				break;
			default:
				$fetchedArray = (array) $result->fetch_object();
				$card = new card($fetchedArray);
				return $card;
		}
	}
	function SQL_existing_card($name, $db){
		global $CardTable;
		$name = sqlString($name, $db);
		return "Select * from $CardTable
				Where name = '$name'";
	}
?>