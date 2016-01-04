<?php
	/**
	* timelinestyle - Returns customtimeline for user
	* 
	* @package timelinestyle
	* @author ColdTrick IT Solutions
	* @copyright Coldtrick IT Solutions 2009
	* @link http://www.coldtrick.com/
	*/
	header('Content-Type: image/jpeg');
	
	if(get_input('id')){
		$filehandler = new ElggFile();
		$filehandler->owner_guid = get_input('id');
		if(get_input('thumb') == "true"){
			$filehandler->setFilename('customtimeline_thumb');
			if (!$filehandler->exists()){
					
				$filehandler->setFilename('customtimeline');
			}
		} else {
			$filehandler->setFilename('customtimeline');
		}
		
		if ($filehandler->exists()){
		
			echo $filehandler->grabFile();
			
			
		} 
	}
	
?>