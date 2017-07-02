# magic-api
**magic-api** is a Magic: The Gathering card database and API for PHP.  

## Overview
MtG card information is available from the official Gatherer reference, but it doesn't provide an API. **magic-api** requests cards from Gatherer, strips the page down to the card data, and caches it in a MySQL database. If the user's search string is new and unique, that is also cached (in a seperate table known as the 'map'), so we can route future searches directly to the DB entry for a card.

The **Scry** front-end is also included in this repo. This is the name given to my personal installation, [Scry.me.uk](http://scry.me.uk), which you can freely re-brand and re-publish (preferably using a different service name).

### Try it
**Scry** is my pet installation of **magic-api**. It has learnt that "Tajic", and "Blade of the legion" both unambiguously refer to 'Tajic, blade of the legion'. Here, you can simulate a user search for "Tajic": [http://scry.me.uk/api.php?metrics=1&name=Tajic](http://scry.me.uk/api.php?metrics=1&name=Tajic)

## Features
* Search for cards by name
* Returns JSON object of card (see API)
* Provides autocomplete for front-end development
* 2-10 seconds to initially fetch a card from Wizards' Gatherer
* About 0.01 seconds to return a cached card

## Setup
**Requires PHP5** and a php.ini with `allow_url_fopen`
Put your database connection information in `db.php` and install all the files together in a directory. `api.php` is the linker; check out the required files in there.  

Run the create scripts from the /sql folder on your MySQL DB.
If you change the table names from the defaults, alter the table name variables at the top of `DAL.php`. Map records have a datetime -- if you want to use a timezone other than UTC, alter the `$db_now` variable in `DAL.php`

## API

### Input
**Parameters:** `name`, `metrics`  
**Example:** `http://scry.me.uk/api.php?name=Forest` or `http://scry.me.uk/api.php?name=Forest&metrics=1`  
N.b. `metrics` is really lazy... if you specify anything that can be loosely interpreted as `true` then it will activate.

### Output
A JSON object. I recommend JSONview for Firefox or Chrome to view the returned values from **magic-api**. Fields with no value will not appear in the returned object: 

#### Success:

	name,
	mana_cost,
	converted_mana_cost,
	types,
	card_text,
	flavor_text,
	watermark,
	power_toughness,
	expansion,
	rarity,
	card_number,
	artist,
	request_time

**When metrics is on,** there will be an additional `caching` field which can contain: 'found in map', 'added to map', 'mapping failed', 'added to cache and map', 'cache or map failed'.  

#### Error:

	error,
	request_time

Error text is user-friendly and can be output as-is.

#### Encoding
Anything represented with an icon on Gatherer (such as Mana and Tap/Untap) will be parsed using the original alt-text, and placed in {braces}. Single-coloured mana is shortened to a single letter: W/U/B/R/G (U is blue). The icon 'Variable colorless' is shorted to `{X}` as it appears on cards. Multi-coloured mana is represented like `{Red or White}` and is not shortened. Phyrexian mana comes out like `{Phyrexian Green}`.  

Lines of card text are delimited by an underscore flanked by spaces, for example:
	
	"card_text": "Flying _ Lifelink (Damage dealt by this creature also causes you to gain that much life.)"
	
You can easily split/explode the strings on '_' to get seperate lines.  
Quotes are escaped with backslashes:
	
	"flavor_text": "\"One day every pillar will be a tree and every hall a glade.\"—Trostani"
	
-----

## Support
People occassionally email me for help and advice on setting up an MtG related website. Feel free to send any queries to github@stegriff.co.uk. If you would like me to host a private **magic-api** node to power your mobile app or something, I can arrange that for £1 per month ($1.70).  

Amaze such magic? so web? wow plz send much dogecoin: `DUPMGGzzZYr1qihXy7EhYtPjxaevAjvsEc`  

-----

Stephen Griffiths - @SteGriff - github@stegriff.co.uk
