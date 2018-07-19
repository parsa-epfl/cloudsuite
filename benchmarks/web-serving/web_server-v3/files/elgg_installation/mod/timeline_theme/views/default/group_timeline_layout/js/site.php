<?php

?>
//<script>
elgg.provide("elgg.group_timeline_layout");

elgg.group_timeline_layout.check_colors = function() {
	var enable = $('#enable_colors').val();
	
	if(enable != 'yes') {
		$('#colorpicker_container').hide();
	} else {
		$('#colorpicker_container').show();
	}
}

elgg.group_timeline_layout.check_background = function() {
	var enable = $('#enable_background').val();

	if(enable != 'yes') {
		$('#background_container').hide();
	} else {
		$('#background_container').show();
	}
}

elgg.group_timeline_layout.init = function() {

	if($('#editForm').length){
		elgg.group_timeline_layout.check_colors();
		elgg.group_timeline_layout.check_background();
		
		$('#backgroundpicker').farbtastic('#backgroundcolor');
		$('#borderpicker').farbtastic('#bordercolor');
		$('#titlepicker').farbtastic('#titlecolor');
	}
}

//register init hook
elgg.register_hook_handler("init", "system", elgg.group_timeline_layout.init);