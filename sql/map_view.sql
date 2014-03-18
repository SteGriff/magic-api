create view mtg_mapped as 
(
select
c.ID, c.Name, c.card_number,
m.search, m.card_id
from
mtg_cards c
left outer join
mtg_map m
on c.ID = m.card_id
)