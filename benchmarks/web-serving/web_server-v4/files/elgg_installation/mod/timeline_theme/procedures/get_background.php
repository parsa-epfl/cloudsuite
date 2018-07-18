<?php 
	
	$guid = (int) get_input("layout_guid");
	
	if(!empty($guid) && ($layout = get_entity($guid))) {
		if(elgg_instanceof($layout, "object", GROUP_TIMELINE_LAYOUT_SUBTYPE)){
			// Check if background isset
			if(!empty($layout->enable_background) && ($layout->enable_background == "yes")) {
				$dataroot = elgg_get_config("dataroot");
				
				if(is_dir($dataroot . "group_timeline_layout/backgrounds/")) {
					$filename = $dataroot . "group_timeline_layout/backgrounds/" . $layout->getOwnerGUID() . ".jpg";
					
					if (file_exists($filename)) {
						if($etag = md5(serialize(filemtime($filename)))) {
							header("Etag: ". $etag);
							$request_etag = $_SERVER["HTTP_IF_NONE_MATCH"];
								
							if(!empty($request_etag) && ($request_etag == $etag)) {
								header("HTTP/1.0 304 Not Modified");
				    			exit();
							}
						}
						
						if($background = file_get_contents($filename)) {
							header("Content-type: image");
							header("Expires: " . date("r", time() + 864000));
							header("Pragma: public");
							header("Cache-Control: public");
							header("Content-Length: " . strlen($background));
							
							echo $background;
						}
					} else {
						header("HTTP/1.0 404 Not Found");
						exit();
					}
				}
			}
		}
	}