<?php

	$group = $vars["entity"];
	$layout = $vars["group_timeline_layout"];
	
	// get values
	if(!empty($layout)){
		$reset = true;
		
		$enable_colors_value = $layout->enable_colors;
		$enable_background_value = $layout->enable_background;
		
		$background_color = $layout->background_color;
		$border_color = $layout->border_color;
		$title_color = $layout->title_color;
	} else {
		$reset = false;
		
		$enable_colors_value = "no";
		$enable_background_value = "no";
		
		$background_color = "#123456";
		$border_color = "#123456";
		$title_color  = "#123456";
	}
	
	$yesno_options = array(
		"yes" => elgg_echo("option:yes"),
		"no" => elgg_echo("option:no")
	);
	
	
	?>
	<div class="elgg-module elgg-module-info mbm">
		<div class="elgg-head"><h3><?php echo elgg_echo("group_timeline_layout:edit:colors"); ?></h3></div>
		<div class="elgg-body">
		<?php 
			echo elgg_echo("group_timeline_layout:edit:colors:enable") . "&nbsp;"; 
			
			echo elgg_view("input/dropdown", array(
					"id" => "enable_colors", 
					"name" => "enable_colors",
					"class" => "mbm",
					"value" => $enable_colors_value, 
					"onchange" => "elgg.group_timeline_layout.check_colors();", 
					"options_values" => $yesno_options
			));
		?>
			
			<table id="colorpicker_container">
				<tr>
					<td>
						<label for="backgroundcolor"><?php echo elgg_echo("group_timeline_layout:edit:backgroundcolor"); ?></label>
						<div id="backgroundpicker"></div>
						<?php echo elgg_view("input/text", array("id" => "backgroundcolor", "name" => "background_color", "value" => $background_color)); ?>
					</td>
					<td>
						<label for="bordercolor"><?php echo elgg_echo("group_timeline_layout:edit:bordercolor"); ?></label>
						<div id="borderpicker"></div>
						<?php echo elgg_view("input/text", array("id" => "bordercolor", "name" => "border_color", "value" => $border_color)); ?>
					</td>
					<td>
						<label for="titlecolor"><?php echo elgg_echo("group_timeline_layout:edit:titlecolor"); ?></label>
						<div id="titlepicker"></div>
						<?php echo elgg_view("input/text", array("id" => "titlecolor", "name" => "title_color", "value" => $title_color)); ?>
					</td>
				</tr>
			</table>
		</div>
	</div>

	<div class="elgg-module elgg-module-info">
		<div class="elgg-head"><h3><?php echo elgg_echo("group_timeline_layout:edit:background"); ?></h3></div>
		<div class="elgg-body">
			<?php 
			echo elgg_echo("group_timeline_layout:edit:background:enable") . "&nbsp;";
	
			echo elgg_view("input/dropdown", array("id" => "enable_background", "name" => "enable_background", "value" => $enable_background_value, "onchange" => "elgg.group_timeline_layout.check_background();", "options_values" => $yesno_options));
			?>
			
			<div id="background_container">
				<br />
				<?php echo elgg_echo("group_timeline_layout:edit:backgroundfile"); ?> 
				<br />
				<?php echo elgg_view("input/file", array("name" => "backgroundFile")); ?>
			</div>
		</div>
	</div>
	
	<?php 
	echo "<div class='elgg-foot'>";
	echo elgg_view("input/hidden", array("name" => "group_guid", "value" => $group->getGUID()));
	echo elgg_view("input/submit", array("name" => "saveButton", "value" => elgg_echo("save")));
		
	if($reset) {
		echo elgg_view("input/button", array(
				"name" => "resetButton", 
				"value" => elgg_echo("reset"), 
				"type" => "button", 
				"title" => elgg_echo("group_timeline_layout:edit:reset:confirm"),
				"class" => "elgg-button-cancel elgg-requires-confirmation smr",
				"onclick" => "document.location.href = \"" . elgg_add_action_tokens_to_url("action/group_timeline_layout/reset?group_guid=" . $group->getGUID()) . "\""
		));
	}
	
	echo "</div>";
	