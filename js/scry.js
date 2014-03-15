$(function(){
	var $name = $('#name');
	$name.focus();
	showCard(false);
	$('form').submit(function(e){
		e.preventDefault();
		getCard();
	});
	$name.keypress(scheduleAutocomplete);
});

var timeoutHandle = null;
var lastTerm = "";

function scheduleAutocomplete(){
	window.clearTimeout(timeoutHandle);
	if ($('#name').val().length > 2){
		timeoutHandle = window.setTimeout(autocomplete, 500);
	}
}

function autocomplete(){
	term = $('#name').val();
	$s = $('#suggestions');
	if (term != lastTerm){
		$.get('./autocomplete.php', { 'name' : term }, function(data){
			clr($s);
			for (d in data){
				$s.append("<li>" + data[d] + "</li>");
			}
			bindSuggestions();
		});
		lastTerm = term;
	}
}

function bindSuggestions(){
	$('li').click(function(e){
		$('#name').val(e.target.textContent);
		$('form').submit();
	});
}

function clr($e){$e.children().remove();}

function getCard(){
	var cardName = $('#name').val();
	getting(cardName);
	
	//Request card data from magic-api
	$.get('./api.php', { 'name' : cardName }, function(data){
		var $panel = $('#results');
		
		//Clear old results
		clr($panel);

		getImage(data.name);
		getting(false);
		
		//Print out returned attributes in definition list
		for(d in data){
			$panel.append("<dt>" + d + "</dt><dd>" + data[d] + "</dd>");
			if (d == 'error'){
				showCard(false);
			}
		}
		
	});
}

function getting(cardName){
	$status = $('#status');
	$status.hide();
	if (cardName){
		$status.text("Getting " + cardName + "...");
		$status.show();
	}
}

function getImage(cardName){
	var url = "http://mtgimage.com/card/" + cardName + ".jpg";
	$("#cardImage").attr("src",url);
	showCard(true);
}

function showCard(on){
	if (on){
		$("#cardImage").show();
		$('article').addClass('shrink');
	}
	else{
		$("#cardImage").hide();
		$('article').removeClass('shrink');
	}
}