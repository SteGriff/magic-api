alter table mtg_cards
modify column name varchar(141) not null

alter table mtg_cards
modify column card_text varchar(500) not null