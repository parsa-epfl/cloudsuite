<?php
	/**
	* timelinestyle
	* 
	* @package timelinestyle
	* @author ColdTrick IT Solutions
	* @copyright Coldtrick IT Solutions 2009
	* @link http://www.coldtrick.com/
	*/
?>
<div class="contentWrapper">
<p><?php echo elgg_echo("timelinestyle:information:welcome");?></p>
<?php if(elgg_get_plugin_setting("colorsCustomizable","timeline_theme") == "yes"){ ?>
<p>
	<div class="user_settings"><h3><?php echo elgg_echo("timelinestyle:colors:title");?></h3></div>
	<?php echo elgg_echo("timelinestyle:information:colors");?>
</p>
<?php } ?>
<?php if(elgg_get_plugin_setting("timelineCustomizable","timeline_theme") == "yes"){ ?>
<p>
	<div class="user_settings"><h3><?php echo elgg_echo("timelinestyle:timeline:title");?></h3></div>
	<?php echo elgg_echo("timelinestyle:information:timeline");?>
</p>
<?php } ?>
</div>
