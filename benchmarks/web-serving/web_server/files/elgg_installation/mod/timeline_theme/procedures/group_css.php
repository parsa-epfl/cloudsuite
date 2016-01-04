<?php

	$guid = (int) get_input("guid");
	
	if(!empty($guid) && ($layout = get_entity($guid))){
		if(elgg_instanceof($layout, "object", GROUP_TIMELINE_LAYOUT_SUBTYPE)){
			// check etag for caching
			$etag = md5($layout->getGUID() . $layout->time_updated);
			$request_etag = $_SERVER["HTTP_IF_NONE_MATCH"];
			
			if(!empty($request_etag) && ($etag == $request_etag)){
				header("Etag: ". $etag);
				header("HTTP/1.0 304 Not Modified");
				exit();
			} else {
				
				$group = $layout->getOwnerEntity();
				if($content = elgg_view("group_timeline_layout/group/css", array("group" => $group, "layout" => $layout))){
					header("Etag: ". $etag);
					header("Content-type: text/css");
					header("Expires: " . date("r", time() + 864000));
					header("Pragma: public");
					header("Cache-Control: public");
					header("Content-Length: " . strlen($content));
					
					echo $content;
				}
			}
		}
	}