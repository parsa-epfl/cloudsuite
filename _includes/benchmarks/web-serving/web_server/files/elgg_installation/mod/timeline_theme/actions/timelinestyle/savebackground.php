<?php
	/**
	* timelinestyle - Saves timeline configuration
	* 
	* @package timelinestyle
	* @author ColdTrick IT Solutions
	* @copyright Coldtrick IT Solutions 2009
	* @link http://www.coldtrick.com/
	*/

	gatekeeper();
	// Make sure action is secure
	action_gatekeeper();
	$current_user = $_SESSION['user']->getGUID();
	$current_user_entity = $_SESSION['user'];
	//TM: Use this to create river
	$performed_on = get_entity($vars['item']->object_guid);
	$user = elgg_get_logged_in_user_entity();
	
	if(get_input('submitButton') == elgg_echo('timelinestyle:timeline:reset')){
		$timelinestyle_object = $current_user_entity->getObjects("timelinestyletimeline", 1, 0); 
		if($timelinestyle_object){
			if($timelinestyle_object[0]->delete()){	
				system_message(elgg_echo('timelinestyle:timeline:reset:success'));
			} else {
				register_error(elgg_echo('timelinestyle:timeline:error:unknown'));
			}
		}
	} else {
		
		// check for existing timelinestyle object, if not, create it
		$timelinestyle_object = $current_user_entity->getObjects("timelinestyletimeline", 1, 0); 
		if(!$timelinestyle_object){
			$timelinestyle_object = new ElggObject();
			$timelinestyle_object->subtype = "timelinestyletimeline";
			$timelinestyle_object->access_id = 2;
			$timelinestyle_object->save();
			$timelinestyle_object = $current_user_entity->getObjects("timelinestyletimeline", 1, 0); 
		} 
		$timelinestyle_object = $timelinestyle_object[0];
		$access_id = 2; //public
		$error = false;
		
		//timelinefile
		// if use current
		if(get_input('timeline-image')){
			$image = get_input('timeline-image');
			
			// custom image?
			// right file type and not to big?
			if($image == 'customtimeline'){
				if(substr_count($_FILES['timelinefile']['type'],'image/') && isset($_FILES['timelinefile']) && $_FILES['timelinefile']['error'] == 0){
					$filename = "customtimeline";
					$extension = pathinfo($_FILES['timelinefile']['name']);
					$extension = $extension['extension'];
					
					$filehandler = new ElggFile();
					$filehandler->setFilename($filename);
					$filehandler->open("write");
					$filehandler->write(get_uploaded_file('timelinefile'));
					$filehandler->close();
					
					$thumbnail = new ElggFile();
					$thumbnail->setFilename($filename . "_thumb");
					$thumbnail->open("write");
					$thumbnail->write(get_resized_image_from_uploaded_file('timelinefile',150,150,false));
					$thumbnail->close();
					
					$timelineURL = 'pg/timeline_theme/getbackground?id=' . $current_user;
				} else {
					register_error(elgg_echo('timelinestyle:timeline:error:image'));
					forward($_SERVER['HTTP_REFERER']);
				}
			} else {
				$timelineURL = $image;	
			}
			if(create_metadata($timelinestyle_object->guid, 'timeline-image', $timelineURL, 'string', $_SESSION['guid'], $access_id) == false || empty($timelineURL)){
				$error = true;
			}
		}
		// repeat
		if(get_input('timeline-repeat')){
			if(create_metadata($timelinestyle_object->guid, 'timeline-repeat', get_input('timeline-repeat'), 'string', $_SESSION['guid'], $access_id) == false){
				$error = true;
			}
		}
		// attachment
		if(get_input('timeline-attachment')){
			if(create_metadata($timelinestyle_object->guid, 'timeline-attachment', get_input('timeline-attachment'), 'string', $_SESSION['guid'], $access_id) == false){
				$error = true;
			}
		}
		// position
		if(get_input('timeline-position')){
			if(create_metadata($timelinestyle_object->guid, 'timeline-position', get_input('timeline-position'), 'string', $_SESSION['guid'], $access_id) == false){
				$error = true;
			}
		}
		// check for error
		if(!$error){
			if(elgg_get_plugin_setting("showInRiver","timeline_theme") != "no"){
				 add_to_river('river/object/timelinestyle/update','update',$current_user,$current_user);
				
				// TM: update to current elgg 1.8
		/*		
			//	$entity_guid = (int) get_input('entity_guid');
				
				// add to river
                                 elgg_create_river_item(array(
                        'view' => 'river/object/timelinestyle/update',
                        'action_type' => 'create',
                        'subject_guid' => $user->guid,
                   //     'object_guid' => $timelinestyle_object,
                     //   'target_guid' => $entity_guid,
                ));
             	
             	
             	*/	 
				
				
				
			}
			system_message(elgg_echo('timelinestyle:timeline:save:success'));
		} else {
			register_error(elgg_echo('timelinestyle:timeline:error:unknown'));
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