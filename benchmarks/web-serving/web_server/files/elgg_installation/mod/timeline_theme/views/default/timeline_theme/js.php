<?php	

?>

var c = readCookie('timelinestyle');
var palette = "<img src='<?php echo $vars['url'];?>mod/timeline_theme/graphics/palette.png' alt='Palette' />";

$(document).ready(function(){
	
	
	
	if($('style[title="timelinestylesheet"]').val() != undefined){
		var switchText = "";
		if(c!='disabled'){
			switchText = "<?php echo elgg_echo("timelinestyle:reset:todefault"); ?>";
		} else {
			switchText = "<?php echo elgg_echo("timelinestyle:reset:tocustom"); ?>";
		}
		$("#layout_canvas").before("<div id='style_change'><a href='javascript:toggleCustom();'>" + palette + switchText + "</a></div>");
	}
});

function toggleCustom(){
	
	/*$("#timelinestylecolors, #timelinestylebackground").remove();*/
	c = readCookie('timelinestyle');
	if(c!='disabled'){
		createCookie('timelinestyle', 'disabled', 365);
		$('style[title="timelinestylesheet"]').each(function(i){
				
				// chrome/safari hack
				if($.browser.safari){
					cacheArray[i] = $(this).html();
					$(this).html("");
				} else {
					this.disabled=true;
				}
				
		});
		$("#style_change a").html(palette + "<?php echo elgg_echo("timelinestyle:reset:tocustom"); ?>");
	} else {
		eraseCookie('timelinestyle');
		$('style[title="timelinestylesheet"]').each(function(i){
				// chrome/safari hack
				if($.browser.safari){
					$(this).html(cacheArray[i]);
				} else {
					this.disabled=false;
				}
				
				
				
		});
		$("#style_change a").html(palette + "<?php echo elgg_echo("timelinestyle:reset:todefault"); ?>");
	}
}


/* Cookie Functions */
function createCookie(name,value,days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+"="+value+expires+"; path=/";
}

function readCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}

function eraseCookie(name) {
	createCookie(name,"",-1);
}
