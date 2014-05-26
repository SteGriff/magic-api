 -- drop table mtg_log
create table mtg_log (
ID serial,
event nvarchar(20) not null,
search nvarchar(150) not null,
happened datetime not null
)
/* 
Event definitions:
scry_search		Search entry on scry. Needs separate API call.
autocorrect		Autocorrect returns some data
api_bad_req		API failed with bad params
api_map			Map a card
api_add_and_map	Add and map a card
api_found		Return existing card
api_not_found	Card doesn't exist or search failed
data_error		Mystery data problem
 */