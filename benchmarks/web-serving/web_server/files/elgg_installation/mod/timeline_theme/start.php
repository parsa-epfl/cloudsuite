<?php
/***************************************************************************
* TwizaNex Smart Community Software
* ---------------------------------
* Start.php: timeline_theme for Elgg 1.8.15
*        
* begin : Mon Mar 23 2011
* copyright : (C) 2011 TwizaNex Group
* website : http://www.TwizaNex.com/
* This file is part of TwizaNex - Smart Community Software
*
* @package Twizanex
* @link http://www.twizanex.com/
* TwizaNex is free software. This work is licensed under a GNU Public License version 2.
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
* @author Tom Ondiba <twizanex@yahoo.com>
* 
* @package Timeline_theme 
* @package Friends finder theme 
* @author sijo @ Cubet Technologies
* @copyright Twizanex Group 2014
* TwizaNex is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
* without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
* See the GNU Public License version 2 for more details.
* For any questions or suggestion write to write to twizanex@yahoo.com
*
***************************************************************************/


/**
 * Init timeline finder plugin.
 */
function timeline_init() {

       // register a library of helper functions
      elgg_register_library('elgg:timeline', elgg_get_plugins_path() . 'timeline_theme/lib/timeline.php'); // Elgg way... 
     
     // Let us load the library

    elgg_load_library('elgg:timeline');
    
    


     // register a library of helper functions
  // 	require_once(dirname(__FILE__) . "/lib/timeline.php"); // Non elgg way of loading the lib functions 

	// add to the main css
	
	

	
	
	
	// elgg_extend_view('css/elgg', 'timeline_theme/timeline_theme_css/demo'); // Timeline_theme index page login
	// elgg_extend_view('css/elgg', 'timeline_theme/timeline_theme_css/style'); // Timeline_theme custom css
	// elgg_extend_view('css/elgg', 'timeline_theme/timeline_theme_css/animate-custom'); // TM: timelne index
        elgg_extend_view('css/elgg', 'timeline_theme/timeline_theme_css/topbar'); // TM: Top bar css 
	
	
	
        
        
        
	
	
	elgg_extend_view('css/elgg', 'timeline_theme/css');
	elgg_extend_view('css/elgg', 'timeline_theme/timelinemenu'); // Timeline_theme menu buttons
	elgg_extend_view('css/elgg', 'timeline_theme/searchcss'); // TM: added css for search bar  
        
        elgg_extend_view('css/elements/elgg','css/elements/layout');
	 elgg_extend_view('css/elements/elgg','css/elements/navigation');

	elgg_unregister_menu_item('topbar', 'elgg_logo');

	//elgg_unregister_menu_item('topbar', array(
	//		'name' => 'messages',
	//		
	//	));

	elgg_extend_view('page/elements/elgg','page/elements/walled_garden');
        
        
      
        elgg_extend_view('page/elements/sidebar', 'sidebar/extedsidebar_alt', 1000);
      
      
       elgg_unextend_view('page/elements/header', 'search/header');
      
         // when now use elgg_get_context()
      if ( elgg_get_context() == 'groups'){
      
      elgg_extend_view('page/elements/header', 'page/elements/header_group');
      
       }else {
       
       // let us extend the header with our header contents
       elgg_extend_view('page/elements/header', 'page/elements/header_user');
       
       }
       
       
        if (elgg_is_logged_in()) {
        
    //    elgg_extend_view('page/elements/header', 'search/header');
        }
	// Replace the default index page
	elgg_register_plugin_hook_handler('index', 'system', 'timeline_index', 300);

	// Register a page handler, so we can have nice URLs
	elgg_register_page_handler('timeline', 'timeline_page_handler');
	
	
	
	
	elgg_extend_view('page/elements/header.php', 'page/elements/ownericon/owner_block_icon.php', '500');//Tom : Invocational codes
	
	
       /**
	 * Customize menus
	 */
//	elgg_register_event_handler('pagesetup', 'system', 'timeline_pagesetup_handler', 1000);	

}


function timeline_pagesetup_handler() {

 //   elgg_push_context('timeline');

	$owner = elgg_get_page_owner_entity();

	
	  //TM:this is start button for the add frinds on the title section on facebook theme
	if (elgg_is_logged_in()) {

		$user = elgg_get_logged_in_user_entity();
			
	
	}

}





/**
 *
 * @param array $page
 * @return NULL
 */
function timeline_page_handler($page) {

// elgg_push_context('timeline');
// Let us load the library

//    elgg_load_library('elgg:timeline');

        $file_dir = elgg_get_plugins_path() . 'timeline_theme/pages/timeline';
      
        if (isset($page[0])) {
                switch ($page[0]) {

                }
        }else{
                 include (elgg_get_plugins_path() . 'timeline_theme/index.php');
        }
      
             }
             
          
             

function timeline_index($hook, $type, $return, $params) {


	if (!include_once(dirname(__FILE__) . "/index.php")) {
		return false;
	}

	// return true to signify that we have handled the front page
	return true;
}

elgg_register_event_handler('init', 'system', 'timeline_init');



/**
	* timelinestyle
	* 
	* @package timelinestyle
	* @author ColdTrick IT Solutions
	* @copyright Coldtrick IT Solutions 2009
	* @link http://www.coldtrick.com/
	*/
	
	// Load Elgg engine
	require_once(dirname(dirname(dirname(__FILE__))) . "/engine/start.php");
	
	function timelinestyle_init() {
		global $CONFIG;
		
		
		
		elgg_register_plugin_hook_handler('profile:fields', 'profile', 'timeline_theme_profile_fields_plugin_handler');

   
    // register facebook theme field action
    
    elgg_register_action('timeline_theme/settings/save', elgg_get_plugins_path() . "timeline_theme/actions/timeline_theme/settings.php", 'admin');

    if (elgg_is_active_plugin('profile_manager')) {
        $profile_options = array(
                        "show_on_register" => "no",
                        "mandatory" => "no",
                        "user_editable" => "yes",
                        "output_as_tags" => "no",
                        "admin_only" => "no",
                        "count_for_completeness" => "yes"
                );
  
    }		
		
		
 if(elgg_get_plugin_setting("colorsCustomizable","timeline_theme") == "yes" || elgg_get_plugin_setting("timelineCustomizable","timeline_theme") == "yes"){
			elgg_extend_view('css','timeline_theme/css');
			elgg_extend_view('js/initialise_elgg','timeline_theme/js');
			
			// Let the users change their timeline or css
			elgg_register_menu_item('title', array(
		        'name' => 'timelinestyle',
		        'href' => 'timelinestyle',
		        'text' => elgg_view_icon('home') . elgg_echo('Change Banner'),
		        'priority' => 460,
		         'section' => 'alt',
	                   ));
		
			
			
			
			// Register a page handler, so we can have nice URLs
			elgg_register_page_handler('timelinestyle','timelinestyle_page_handler');
			
			if (elgg_get_context() == "profile" && elgg_is_active_plugin('profile')) {
				add_submenu_item(elgg_echo('timelinestyle:shorttitle') , $CONFIG->wwwroot . "pg/timelinestyle");
			}
			
			elgg_register_event_handler('pagesetup','system','timelinestyle_pagesetup');
		}
	}
	
	function timelinestyle_pagesetup(){
		global $CONFIG;
		
		if(!elgg_get_page_owner_guid() && elgg_is_logged_in()){
			elgg_set_page_owner_guid($_SESSION['user']->getGUID()); 
		}
		if (elgg_is_logged_in()) {
			// elgg_register_menu_item(elgg_echo('timelinestyle:shorttitle'), $CONFIG->wwwroot . 'pg/timelinestyle/');
			
			
			// TM: start site menu item
	
			// add top menu item
                elgg_register_menu_item("site", array(
                        "name" => "timelinestyle",
                        "text" => elgg_echo("timelinestyle:shorttitle"),
                        "priority" => 10,
                        "href" => "pg/timelinestyle/"
                ));
			
		// TM: End add top menu item	
			
			
		}
		if(elgg_get_context() == 'timelinestyle'){
			if(elgg_get_plugin_setting("colorsCustomizable","timeline_theme") == "yes"){
				add_submenu_item(elgg_echo('timelinestyle:menu:colors'), $CONFIG->wwwroot . "mod/timeline_theme/colors.php" , '');
			}
			if(elgg_get_plugin_setting("timelineCustomizable","timeline_theme") == "yes"){
				add_submenu_item(elgg_echo('timelinestyle:menu:timeline'), $CONFIG->wwwroot . "mod/timeline_theme/background.php" , '');
			}
		}
		 if ($_SERVER['PHP_SELF'] != "/index.php" && elgg_get_page_owner_guid() != 0) {
			elgg_extend_view('metatags','timeline_theme/metatags');
		}
	}
	
	
	
	 //Let us add timeline_theme fields to the core profile
	
	function timeline_theme_profile_fields_plugin_handler($hook, $type, $return_value, $params) {

    // add timeline_theme fields to the core profile

    
     if((elgg_get_plugin_setting("facebooks_field","timeline_theme") == 'yes') && (!$return_value['facebooks'])) {
        $return_value['facebooks'] = 'url';
    }
    

    
    if((elgg_get_plugin_setting("googlepluss_field","timeline_theme") == 'yes') && (!$return_value['googlepluss'])) {
        $return_value['googlepluss'] = 'url';
    }
    
     if((elgg_get_plugin_setting("youtubes_field","timeline_theme") == 'yes') && (!$return_value['youtubes'])) {
        $return_value['youtubes'] = 'url';
    }
    if((elgg_get_plugin_setting("linkedins_field","timeline_theme") == 'yes') && (!$return_value['linkedins'])) {
        $return_value['linkedins'] = 'url';
    }
 

     if((elgg_get_plugin_setting("twitters_field","timeline_theme") == 'yes') && (!$twitter_value['twitters'])) {
        $return_value['twitters'] = 'url';
    }
    

    if((elgg_get_plugin_setting("feedburner_email_field","timeline_theme") == 'yes') && (!$return_value['feedburner_email'])) {
        $return_value['feedburner_email'] = 'url';
    }
    
  
    
    if((elgg_get_plugin_setting("feeds_feedburner_rss_field","timeline_theme") == 'yes') && (!$return_value['feeds_feedburner_rss'])) {
        $return_value['feeds_feedburner_rss'] = 'url';
    }
    

    elgg_set_config('profile_timeline_theme_prefix', 'timeline_theme_');

    return $return_value;
}
	
	
	
	
	
	
	function timelinestyle_page_handler($page){
		global $CONFIG;
		
		if(!empty($page[0]) && $page[0] == "getbackground"){
			include($CONFIG->pluginspath . "timeline_theme/getbackground.php");
		} elseif(elgg_is_logged_in()){
			// only interested in one page for now
			include($CONFIG->pluginspath . "timeline_theme/index_timeline.php"); 
		} else {
			forward($CONFIG->wwwroot);
		}
		
		return true; // TM: Force page handler to return true - to avoid error: Page requested can not be found
		
	
	}
	
	function get_timeline_style_from_metadata($user, $metadata_name){
		$returnArray = false;
		
		$user = get_entity($user);
		
		$timelinestyle_object = $user->getObjects($metadata_name, 1, 0);
		
		$customConfig = get_metadata_for_entity($timelinestyle_object[0]->guid);
		
		if($customConfig){
			foreach($customConfig as $metadataObject){
				$returnArray[$metadataObject['name']] = $metadataObject['value'];
			}
		}
		
		return $returnArray;		
	}
	
	elgg_register_event_handler('init','system','timelinestyle_init');
	
	// Register actions
        $action_path = elgg_get_plugins_path() . 'timeline_theme/actions/timelinestyle';
        elgg_register_action("timelinestyle/savebackground", "$action_path/savebackground.php");
        elgg_register_action("timelinestyle/savecolors", "$action_path/savecolors.php");
        
        
        
        
        
        
        
        /**
 * Timeline my Pages
 * 
 * Add ajax features to native paginator
 *
 * @package timelinescroll
 */

/**
 * Constant to define the default value of infinitive scroll enabled/disabled
 */
define('TIMELINE_CAN_INFINITIVE', TRUE);

elgg_register_event_handler('init', 'system', 'timelinescroll_init');

/**
 * Initialize the plugin 
 */
function timelinescroll_init() {
    elgg_register_plugin_hook_handler('view', 'navigation/pagination', 'timeline_view_paginator_hook');
    
    elgg_register_css('timelinescroll', elgg_get_simplecache_url('css', 'timelinescroll'));
    elgg_register_simplecache_view('css/timelinescroll');
    
    elgg_register_js('timelinescroll', elgg_get_simplecache_url('js', 'timelinescroll'));
    elgg_register_simplecache_view('js/timelinescroll');
    
    elgg_load_js('timelinescroll');
    elgg_load_css('timelinescroll');
    
}

function yes_infinitive_scroll() {
    $default_value = TIMELINE_CAN_INFINITIVE;
    $plugin = elgg_get_plugin_from_id('timeline_theme');
    
    $infinite_scroll = $plugin->timeline_scroll;
    
    switch ($infinite_scroll) {
        case 'yes':
            return TRUE;
            break;
        
        case 'no':
            return FALSE;
            break;
    }
    
    return $default_value;
}

function timeline_view_paginator_hook($hook, $type, $return, $params) {
    
    $can_infinite = yes_infinitive_scroll();
    
    static $infinite_loaded;
    
    //only one paginator with infinite scroll per page, otherwise it will bug
    if ($can_infinite) { 
        if (isset($infinite_loaded) && $infinite_loaded == TRUE) {
            return $return;
        }
    }
    
    if (!empty($return) && !elgg_in_context('admin')) {
        $infinite_loaded = TRUE;
        return elgg_view('timelinescroll/navigation/pagination', array_merge($params, array('hidden_paginator' => $return)));
    }

    return $return;
}
        
   
   /*
   *
   * timeline group layout starts from here
   *
   */
   
	define("GROUP_TIMELINE_LAYOUT_SUBTYPE", 		"timeline_layout");
	define("GROUP_TIMELINE_LAYOUT_BACKGROUND", 	"group_background");
	define("GROUP_TIMELINE_LAYOUT_RELATION", 		"timeline_layout_relation");

	require_once(dirname(__FILE__) . "/lib/functions.php");
	require_once(dirname(__FILE__) . "/lib/page_handlers.php");
	
	elgg_register_event_handler("init", "system", "group_timeline_layout_init");
	elgg_register_event_handler("pagesetup", "system", "group_timeline_layout_pagesetup");
	
	function group_timeline_layout_init() {
		// extend css/js
		elgg_extend_view("css/elgg", "group_timeline_layout/css");
		elgg_extend_view("js/elgg", "group_timeline_layout/js/site");

		// register external JS libraries
		elgg_register_js("thickbox_js", 	"mod/timeline_theme/vendors/thickbox/thickbox-compressed.js");
		elgg_register_js("farbtastic_js", 	"mod/timeline_theme/vendors/farbtastic/farbtastic.js");
		
		// register external CSS
		elgg_register_css("thickbox_css", 	"mod/timeline_theme/vendors/thickbox/thickbox.css");
		elgg_register_css("farbtastic_css",	"mod/timeline_theme/vendors/farbtastic/farbtastic.css");	

		// register page handler
		elgg_register_page_handler("group_timeline_layout", "group_timeline_layout_page_handler");

		// register actions
		// Register actions
        $action_timeline_path = elgg_get_plugins_path() . 'timeline_theme/actions/group_timeline_layout';
		
		elgg_register_action("group_timeline_layout/save",  "$action_timeline_path/save.php");
		elgg_register_action("group_timeline_layout/reset",  "$action_timeline_path/reset.php");
	}

	function group_timeline_layout_pagesetup() {
		
		$group = elgg_get_page_owner_entity();

		if (!empty($group) && elgg_instanceof($group, "group")) {
			if (group_timeline_layout_allow($group) && $group->canEdit()) {
				// add menu item for group admins to edit layout
				elgg_register_menu_item("page", array(
					"name" => "group_layout", 
					"text" => elgg_echo("group_timeline_layout:edit"), 
					"href" => "group_timeline_layout/" . $group->getGUID(),
					"context" => "group_profile"
				));
			}
			
			if($layout = group_timeline_layout_get_layout($group)){
			
				elgg_register_css("timeline_group_layout", "group_timeline_layout/group_css/" . $layout->getGUID() . "/" . $layout->time_updated . ".css");
				elgg_load_css("timeline_group_layout");
			}
		}
	}   
   
   
   
   
   
   
   
        