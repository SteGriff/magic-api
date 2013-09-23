create table mtg_cards (
ID SERIAL,
	primary key(ID),
name varchar(141) not null,
mana_cost varchar(50),
converted_mana_cost int,
types varchar(50) not null,
card_text varchar(500) not null,
flavor_text varchar(255),
power_toughness varchar(10),
expansion varchar(50),
rarity varchar(20),
card_number int,
artist varchar(50)
);

-- name, mana_cost, converted_mana_cost, types, card_text, flavor_text, power_toughness, expansion, rarity, card_number, artist, 