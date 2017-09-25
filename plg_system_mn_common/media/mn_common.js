if(typeof mn_common_history_obj == 'undefined'){
	var mn_common_history_obj = new Array();
}
	
//pokud je HTML5 prohlizec, zapneme historii pro AJAX!
if(Modernizr.history){	
	var first_load = true; //pri prvni nacteni stranky se zavola popstate, timto zamezime nacteni znovu hned po zobrazeni prvni stranky
	var start_href = window.location.href;
	
	
	
	//když se klikne na novy href (jestě neni v historii) uložime do historie.
	function mn_common_click(href, elementId) {		
		mn_common_load_content(href + '&format=raw', elementId);		
		first_load = false;
		
		//nutno aktualizovat vsechny browsery!
		var newState = history.state;
		var result = jQuery.grep(newState, function(e){ return e.element_id == elementId; }); //vyhledame v poli odpovidajici browserid a nahradime url
		if (result.length == 0) {
			// not found
		} else if (result.length == 1) {
			result[0].url = href;
			history.pushState(newState, null, start_href); 
		} else {
			// multiple items found
		}
		
		//history.pushState({url : href, browserid : browserId}, null, href);
		console.log('Current state URL (after click): ' + JSON.stringify(history.state));
	}
	
	function mn_common_load_content(href, elementId) {
		
		jQuery('#' + elementId).html('<p><img src="' + window.location.origin + '/media//plg_mn_common/ajax-loader.gif" width="220" height="19" /></p>');
		jQuery('#' + elementId).load(href, function() {
			//mn_init_colorbox(); //reinicializace colorbox po zmene obsahu stranky
			mn_init_history(); //reinicializace onlick na history elementy
		});
		
	};	
	//revert content to previous state on back or forward action
	jQuery(window).bind('popstate', function(){
		console.log('popstate fired!');
		if(first_load == false) {
			//pro kazdy browser nacteme obsah
			for (var i=0;i<history.state.length;i++)
			{
				mn_common_load_content(history.state[i].url + '&format=raw', history.state[i].element_id); //do load_content je nutne poslat href (co nahrat) a id prvku (kam nahrat)
			}
			
			console.log('Current state URL  (after window load): ' + JSON.stringify(history.state));
		}
	});
	
	jQuery(document).ready(function(){
		mn_init_history = function() {
			jQuery(".mn-common-history-link").click(function(){
				mn_common_click(jQuery(this).attr("href"), jQuery(this).closest(".mn-common-history-container").attr("id"));
				return false;   
			});
		}
		mn_init_history();
	});
	
	jQuery(document).ready(function(){ //je nutne aktualizovat spolecny state_object - nutno az po vytvoreni novych mn_common_history_obj objektů
		if(typeof mn_common_history_obj != 'undefined'){
			var state_object = new Array();
			for (var i=0;i<mn_common_history_obj.length;i++)
			{ 
				console.log(mn_common_history_obj[i]);
				//toto předělat aby mn_history_obj obsahoval již url, pak to bude universální!!! :)
				state_object[i] = {url : '/' + mn_common_history_obj[i].href, element_id : mn_common_history_obj[i].element}
			}
			history.replaceState(state_object, null, start_href);
			console.log('Current state URL (after initilize): ' + JSON.stringify(history.state));
		}
	});
	
}	//konec if modernizr.history

