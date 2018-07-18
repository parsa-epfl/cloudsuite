<?php
	/**
	* timelinestyle - timeline configuration 
	* 
	* @package timelinestyle
	* @author ColdTrick IT Solutions
	* @copyright Coldtrick IT Solutions 2009
	* @link http://www.coldtrick.com/
	*/
	gatekeeper();

	$current_user = $_SESSION['user']->getGUID();
	$currentConfig = get_timeline_style_from_metadata($current_user, 'timelinestyletimeline');	
	global $CONFIG;
	
	$wallpaper_path = "mod/timeline_theme/graphics/wallpapers";
	$imageArray = array();	
	$dir_handle = opendir($CONFIG->path . $wallpaper_path);
	while (($file = readdir($dir_handle)) !== false) {
		if ($file!='.' && $file!='..' && !is_dir($dir.$entry)){
			$dotPosition = strrpos($file,".");
			if($dotPosition){
				$shortFileName = substr($file,0,$dotPosition);
				$imageArray[elgg_echo($shortFileName)] = $wallpaper_path . "/" . str_replace(" ", "%20",$file);
			}
		}
	}
		
	$body = "<br />";
	$body .= "<table id='gallery'><tr>";
	
	$i=0;
	
	// own image
	if($currentConfig["timeline-image"]){
		$body .= "<td>";
		$body .= "<input type='radio' name='timeline-image' value='" . $currentConfig['timeline-image'] . "' onclick='myFunction() = \"url(" . $CONFIG->wwwroot . $currentConfig['timeline-image'] . ")\"' CHECKED> " . elgg_echo("timelinestyle:timeline:currenttimeline") . "<br />";
		$body .= "<a href='" . $CONFIG->wwwroot . $currentConfig['timeline-image'] . "' title='" . elgg_echo('timelinestyle:timeline:customtimeline') . "'><img src=";
		$body .= $CONFIG->wwwroot . $currentConfig['timeline-image']; 
		if(substr_count($currentConfig['timeline-image'], "getbackground?id=")) $body .= "&thumb=true";
		$body .= " style='width:150px;' alt='" . elgg_echo('timelinestyle:timeline:customtimeline') . "'/></a>";
		$body .= "</td>";
		$i++;
	} 
	
	if(elgg_get_plugin_setting("allowUploadtimeline","timeline_theme") != "no"){
		// check for previously uploaded timeline
		$filehandler = new ElggFile();
		$filehandler->owner_guid = $current_user;
		$filehandler->setFilename('customtimeline');
		if($filehandler->exists()){
			$imageUrl = 'pg/timelinestyle/getbackground?id=' . $current_user;
			if($imageUrl != $currentConfig["timeline-image"]){
				$body .= "<td>";
				$body .= "<input type='radio' name='timeline-image' value='" . $imageUrl . "' onclick='myFunction() = \"url(" . $CONFIG->wwwroot . $imageUrl . ")\"'> " . elgg_echo("timelinestyle:timeline:previouslyuploadedtimeline") . "<br />";
				$body .= "<a href='" . $CONFIG->wwwroot . $imageUrl . "' title='" . elgg_echo('timelinestyle:timeline:previouslyuploadedtimeline') . "'><img src='" . $CONFIG->wwwroot . $imageUrl . "&thumb=true' style='width:150px;' alt='" . elgg_echo('timelinestyle:timeline:previouslyuploadedtimeline') . "'/></a>";
				$body .= "</td>";
				$i++;
			}
		}
	}
	
	// load default images
	foreach($imageArray as $name=>$image){
		if($i == 4) {
			$body .= "</tr><tr>";	
			$i = 0;
		}
		if($image != $currentConfig['timeline-image']){
			$body .= "<td>";
			$body .= "<input type='radio' name='timeline-image' value='" . $image . "' onclick='myFunction() = \"url(" . $CONFIG->wwwroot . $image . ")\"'> " . $name . "<br />";
			$body .= "<a href='" . $CONFIG->wwwroot . $image . "' title='" . $name . "'><img src='" . $CONFIG->wwwroot . $image . "' style='width:150px;'/></a>";
			$body .= "</td>";
			
			$i++;
		}
	}
		
	$body .= "</tr></table>";
	
	if(elgg_get_plugin_setting("allowUploadtimeline","timeline_theme") != "no"){
		$body .= "<input type='radio' id='customtimeline' name='timeline-image' value='customtimeline' onclick='uploadtimelineFunction()' > " . elgg_echo("timelinestyle:timeline:customtimeline") . "<br />";
		// max file size of uploaded file in bytes
		$max_file_size = elgg_get_plugin_setting("maxUploadSize","timeline_theme");
		if(!$max_file_size) $max_file_size = "512000";
		$body .= "<input type='hidden' name='MAX_FILE_SIZE' value='" . $max_file_size . "' />";
		$body .= elgg_view("input/file",array("internalname"=>"timelinefile", "js"=>"onclick=\"$('#customtimeline').attr('checked', 'checked');\"")) . "<br /><br />";
	}

	
/*	
	
	$body .= "<table><col STYLE='white-space:nowrap;'>";
	
	// repeat
	$body .= "<tr><td colspan=2>";
	$body .= "<div class='user_settings'><h3>" . elgg_echo("timelinestyle:timeline:repeat:title") . "</h3></div>";
	$body .= "</td></tr><tr><td>";
	foreach(elgg_echo("timelinestyle:timeline:repeat:options") as $value=>$text){
		$checked = "";
		if(array_key_exists('timeline-repeat',$currentConfig)){
			if($currentConfig['timeline-repeat'] == $value){
				$checked = " checked";
			}
		}
		$body .= "<input type='radio' name='timeline-repeat' value='" . $value . "'" . $checked . " onclick='document.body.style.backgroundRepeat = \"" . $value . "\"'> " . $text . "<br />";
	}
	$body .= "</td><td>" . elgg_echo("timelinestyle:timeline:repeat:description") . "</td></tr>";
	
	// attachment
	$body .= "<tr><td colspan=2>";
	$body .= "<div class='user_settings'><h3>" . elgg_echo("timelinestyle:timeline:attachment:title") . "</h3></div>";
	$body .= "</td></tr><tr><td>";
	foreach(elgg_echo("timelinestyle:timeline:attachment:options") as $value=>$text){
		$checked = "";
		if(array_key_exists('timeline-attachment',$currentConfig)){
			if($currentConfig['timeline-attachment'] == $value){
				$checked = " checked";
			}
		}
		$body .= "<input type='radio' name='timeline-attachment' value='" . $value . "'" . $checked . " onclick='document.body.style.backgroundAttachment = \"" . $value . "\"'> " . $text . "<br />";
	}
	$body .= "</td><td>" . elgg_echo("timelinestyle:timeline:attachment:description") . "</td></tr>";
		
	// position
	$body .= "<tr><td colspan=2>";
	$body .= "<div class='user_settings'><h3>" . elgg_echo("timelinestyle:timeline:position:title") . "</h3></div>";
	$body .= "</td></tr><tr><td>";
	$i=0;
	foreach(elgg_echo("timelinestyle:timeline:position:options") as $value=>$text){
		if($i==3){
			$body .= "<br />";
			$i=0;
		}
		$checked = "";
		if(array_key_exists('timeline-position',$currentConfig)){
			if($currentConfig['timeline-position'] == $value){
				$checked = " checked";
			}
		}
		$body .= "<input type='radio' name='timeline-position' title='" . $text . "' value='" . $value . "'" . $checked . " onclick='document.body.style.backgroundPosition = \"" . $value . "\"'>";
		$i++;
	}
	$body .= "</td><td>" . elgg_echo("timelinestyle:timeline:position:description") . "</td></tr>";
		
	$body .= "</table>";
	
*/	
	
	$body .= elgg_view('input/submit', array("internalname"=>"submitButton", 'value' => elgg_echo('save'))) . " ";
	$body .= elgg_view('input/submit', array("internalname"=>"submitButton", 'value' => elgg_echo('timelinestyle:timeline:reset')));
	$configForm = elgg_view("input/form",array('body' => $body,'method' => 'post', 'enctype' => 'multipart/form-data' ,'action' => $vars['url'] . "action/timelinestyle/savebackground"));
	
?>
<script type="text/javascript" src="<?php echo $vars['url'];?>mod/timeline_theme/js/lightbox/js/jquery.lightbox-0.5.pack.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo $vars['url'];?>mod/timeline_theme/js/lightbox/css/jquery.lightbox-0.5.css" media="screen" />
<script type="text/javascript">
	$(document).ready(function() {
		$('#gallery a').lightBox();
	});
</script>
<div class="contentWrapper">

	<div id="noconfig" <?php if($currentConfig){ ?>style="display:none"<?php }?>>
		<p>
		<?php 
			echo elgg_echo("timelinestyle:timeline:noconfig") . "<br />";
			$js = "onclick='$(\"#noconfig\").toggle();$(\"#config\").toggle()'";
			echo elgg_view("input/button", array("value"=>elgg_echo("timelinestyle:timeline:customizebutton"), "js"=>$js));
		?>
		</p>
	</div>
	<div id="config" <?php if(!$currentConfig){ ?>style="display:none"<?php }?>>	
		<?php
			echo elgg_echo("timelinestyle:timeline:selectinfo") . "<br />";
			echo $configForm;
		?>
	</div>	
</div>
