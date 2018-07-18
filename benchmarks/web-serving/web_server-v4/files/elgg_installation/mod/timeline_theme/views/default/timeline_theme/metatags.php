<?php
	/**
	* timelinestyle - Overrides all loaded css (not inline style)
	* 
	* @package timelinestyle
	* @author ColdTrick IT Solutions
	* @copyright Coldtrick IT Solutions 2009
	* @link http://www.coldtrick.com/
	*/
	
	global $CONFIG;
	
	$important = "";
	if(elgg_get_context() != "timelinestyle") $important = " !important";
	
	$currentConfig = get_timeline_style_from_metadata(elgg_get_page_owner_guid(), 'timelinestylecolors');
	
	if($currentConfig){
		//colors configured
		
		?>
		<style type="text/css" title="timelinestylesheet">
		/* timelinestylecolors */
		<?php
		
		
		foreach($currentConfig as $key=>$value){
			$rowData = explode("|", $key);
			if($rowData[0] <> ""){
				echo $rowData[0] . " { \n" . $rowData[1] . ": ". $value . $important . ";\n }\n";
			}
		}
		?>
		</style>
		<?php
	}
	
	$currentConfig = get_timeline_style_from_metadata(elgg_get_page_owner_guid(), 'timelinestyletimeline');
	
	if($currentConfig){
		// background configured
		
		?>
			<style type="text/css" title="timelinestylesheet">
				/* timelinestylebackground */
				body {
					<?php
						foreach($currentConfig as $key=>$value){
							if($key == 'timeline-image'){
								echo $key . ": url(" . $CONFIG->wwwroot . $value .  ")" . $important . ";\n";
								
							} else {
								echo $key . ": " . $value . $important .";\n";
							}
						}
					?>
					
				}
			</style>
		<?php
	}


?>
<script type="text/javascript">
	var cacheArray = [];
	if(c=='disabled'){		
		$('style[title="timelinestylesheet"]').each(function(i){
				
				// chrome/safari hack
				if($.browser.safari){
					cacheArray[i] = $(this).html();
					$(this).html("");
				} else {
					this.disabled=true;
				}
				
		});
	}
</script>