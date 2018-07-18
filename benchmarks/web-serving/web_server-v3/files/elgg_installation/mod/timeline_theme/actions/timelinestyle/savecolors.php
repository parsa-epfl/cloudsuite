<?php
	/**
	* timelinestyle - Save Color Configuration
	* 
	* @package timelinestyle
	* @author ColdTrick IT Solutions
	* @copyright Coldtrick IT Solutions 2009
	* @link http://www.coldtrick.com/
	*/
	gatekeeper();
	// Make sure action is secure or safe
	action_gatekeeper();
	$current_user = $_SESSION['user']->getGUID();
	$current_user_entity = get_entity($current_user);
	
	//TM: Use this to create river
	$performed_on = get_entity($vars['item']->object_guid);
	
	if(get_input('submitButton') == elgg_echo('timelinestyle:colors:reset')){
		$timelinestyle_object = $current_user_entity->getObjects("timelinestylecolors", 1, 0); 
		if($timelinestyle_object){
			if($timelinestyle_object[0]->delete()){	
				system_message(elgg_echo('timelinestyle:colors:reset:success'));
			} else {
				register_error(elgg_echo('timelinestyle:colors:error:unknown'));
			}
		}
	} else {
		// check for existing timelinestyle object, if not, create it
		$timelinestyle_object = $current_user_entity->getObjects("timelinestylecolors", 1, 0); 
		if(!$timelinestyle_object){
			$timelinestyle_object = new ElggObject();
			$timelinestyle_object->subtype = "timelinestylecolors";
			$timelinestyle_object->access_id = 2;
			$timelinestyle_object->save();
			$timelinestyle_object = $current_user_entity->getObjects("timelinestylecolors", 1, 0); 
		} 
		$timelinestyle_object = $timelinestyle_object[0];
		$access_id = 2; //public
		$error = false;

		$data = get_input('timelinestyle');
		if($data){
			foreach($data as $key=>$value){
				if(create_metadata($timelinestyle_object->guid, $key, $value, 'string', $_SESSION['guid'], $access_id) == false){
					$error = true;
				}
			}
		}
		
		// save
		if(!$error){
			if(elgg_get_plugin_setting("showInRiver","timelinestyle") != "no"){
				add_to_river('river/object/timelinestyle/update','update',$current_user,$current_user);
		/*		
				// TM: update to current elgg 1.8
				// add to river
                                 elgg_create_river_item(array(
                        'view' => 'river/object/timelinestyle/update',
                        'action_type' => 'create',
                        'subject_guid' => elgg_get_logged_in_user_guid(),
                        'object_guid' => $performed_on ->guid,
                ));
		*/		
			}
			system_message(elgg_echo('timelinestyle:colors:save:success'));
		} else {
			register_error(elgg_echo('timelinestyle:colors:error:unknown'));
		}
	}
	
	//no cache
	header("Expires: Mon, 26 Jul 1990 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
	header("Cache-Control: no-store, no-cache, must-revalidate"); 
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	forward($_SERVER['HTTP_REFERER']);

?>