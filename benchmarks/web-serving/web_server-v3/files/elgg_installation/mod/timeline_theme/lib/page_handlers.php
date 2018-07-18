<?php

	function group_timeline_layout_page_handler($page) {
	
		switch($page[0]){
			case "get_background":
				if(!empty($page[1]) && !empty($page[2])) {
					set_input("layout_guid", $page[1]);
						
					include(dirname(dirname(__FILE__)) . "/procedures/get_background.php");
				}
				break;
			case "group_css":
				if(!empty($page[1])){
					set_input("guid", $page[1]);
					
					include(dirname(dirname(__FILE__)) . "/procedures/group_css.php");
				}
				break;
			default:
				if(!empty($page[0])) {
				set_input("group_guid", $page[0]);
			}
			include(dirname(dirname(__FILE__)) . "/pages/edit.php");
			break;
		}
	
		return true;
	}