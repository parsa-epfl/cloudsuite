<?php
/**
 * Elgg river auto update plugin
 * 
 * 
 * Contributor: Hadi Katebi
 * Contact: hadi.katebi@gmail.com
 */

define("LIMIT",         15);    // limit on the number of activities to be loaded
define("REFRESH_RATE",  15000); // refresh rate of the activity page in ms

elgg_register_event_handler('init', 'system', 'river_auto_update_init');

function river_auto_update_init() {

	// Register page handler
	elgg_unregister_page_handler('activity', 'elgg_river_page_handler');
	elgg_register_page_handler('activity', 'river_auto_update_page_handler');
	
	// extend js view
	elgg_extend_view("js/elgg", "js/riverautoupdate/functions");
}

function river_auto_update_page_handler($page) {
	global $CONFIG;	
	
	elgg_set_page_owner_guid(elgg_get_logged_in_user_guid());

	// make ajax procedure visible to the activity page
	if ($page[0] == "proc") {		
		include("{$CONFIG->path}mod/riverautoupdate/procedures/" . $page[1] . ".php");			
	} 
	else {
		$page_type = elgg_extract(0, $page, 'all');
		$page_type = preg_replace('[\W]', '', $page_type);
		if ($page_type == 'owner') {
			$page_type = 'mine';
		}	
		set_input('page_type', $page_type);
	}	
	
	require_once("{$CONFIG->path}mod/riverautoupdate/pages/river.php");
	return true;
}
