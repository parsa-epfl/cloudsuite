<?php
	/**
	* timelinestyle - Admin settings
	* 
	 * for Elgg 1.8 
	* @package timelinestyle
	* @author Twizanex Team
	* @copyright Twizanex 2014
	* @link http://www.twizanex@yahoo.com/
	* @copyright iionly 2012-2013
        * iionly@gmx.de
	*/

$plugin = elgg_get_plugin_from_id('timeline_theme');






/**************TIMELINE SCROLL*********************************/

$infinitive_enabled = yes_infinitive_scroll();
$infinitive_value = 'no';
if ($infinitive_enabled) {
    $infinitive_value = 'yes';
}

/********************END OF TIMELINE SCROLL*********************************/


if (!(elgg_get_plugin_setting('facebooks_field', 'timeline_theme'))) {
    elgg_set_plugin_setting('facebooks_field', 'no', 'timeline_theme');
}
if (!(elgg_get_plugin_setting('googlepluss_field', 'timeline_theme'))) {
    elgg_set_plugin_setting('googlepluss_field', 'no', 'timeline_theme');
}
if (!(elgg_get_plugin_setting('youtubes_field', 'timeline_theme'))) {
    elgg_set_plugin_setting('youtubes_field', 'yes', 'timeline_theme');
}
if (!(elgg_get_plugin_setting('linkedins_field', 'timeline_theme'))) {
    elgg_set_plugin_setting('linkedins_field', 'no', 'timeline_theme');
}
if (!(elgg_get_plugin_setting('twitters_field', 'timeline_theme'))) {
    elgg_set_plugin_setting('twitters_field', 'no', 'timeline_theme');
}
if (!(elgg_get_plugin_setting('feedburners_email_field', 'timeline_theme'))) {
    elgg_set_plugin_setting('feedburners_email_field', 'no', 'timeline_theme');
}

if (!(elgg_get_plugin_setting('feeds_feedburners_rss_field', 'timeline_theme'))) {
    elgg_set_plugin_setting('feeds_feedburners_rss_field', 'no', 'timeline_theme');
}
    
    
/*  timeline inifinte scroll */


$form .= '<br><br><label>' . elgg_echo("timeline_my_pages:infinite_scroll:label") . '</label> ';
$form .= elgg_view('input/dropdown', array(
                    'name' => 'params[timeline_scroll]',
                    'options_values' => array('yes' => elgg_echo('option:yes'), 'no' => elgg_echo('option:no')),
                    'value' => $infinitive_value
    ));



$form .= '<br><br><label>' . elgg_echo("profile:facebooks") . '</label> ';
$form .= elgg_view('input/dropdown', array(
                    'name' => 'params[facebooks_field]',
                    'options_values' => array('yes' => elgg_echo('option:yes'), 'no' => elgg_echo('option:no')),
                    'value' => $plugin->facebooks_field
    ));

$form .= '<br><br><label>' . elgg_echo("profile:googlepluss") . '</label> ';
$form .= elgg_view('input/dropdown', array(
                    'name' => 'params[googlepluss_field]',
                    'options_values' => array('yes' => elgg_echo('option:yes'), 'no' => elgg_echo('option:no')),
                    'value' => $plugin->googlepluss_field
    ));


$form .= '<br><br><label>' . elgg_echo("profile:youtubes") . '</label> ';
$form .= elgg_view('input/dropdown', array(
                    'name' => 'params[youtubes_field]',
                    'options_values' => array('yes' => elgg_echo('option:yes'), 'no' => elgg_echo('option:no')),
                    'value' => $plugin->youtubes_field
    ));

$form .= '<br><br><label>' . elgg_echo("profile:linkedins") . '</label> ';
$form .= elgg_view('input/dropdown', array(
                    'name' => 'params[linkedins_field]',
                    'options_values' => array('yes' => elgg_echo('option:yes'), 'no' => elgg_echo('option:no')),
                    'value' => $plugin->linkedins_field
    ));



$form .= '<br><br><label>' . elgg_echo("profile:twitters") . '</label> ';
$form .= elgg_view('input/dropdown', array(
                    'name' => 'params[twitters_field]',
                    'options_values' => array('yes' => elgg_echo('option:yes'), 'no' => elgg_echo('option:no')),
                    'value' => $plugin->twitters_field
    ));

$form .= '<br><br><label>' . elgg_echo("profile:feedburner_email") . '</label> ';
$form .= elgg_view('input/dropdown', array(
                    'name' => 'params[feedburners_email_field]',
                    'options_values' => array('yes' => elgg_echo('option:yes'), 'no' => elgg_echo('option:no')),
                    'value' => $plugin->feedburners_email_field
    ));


$form .= '<br><br><label>' . elgg_echo("profile:feeds_feedburner_rss") . '</label> ';
$form .= elgg_view('input/dropdown', array(
                    'name' => 'params[feeds_feedburners_rss_field]',
                    'options_values' => array('yes' => elgg_echo('option:yes'), 'no' => elgg_echo('option:no')),
                    'value' => $plugin->feeds_feedburners_rss_field
    ));

   
$form .= '<br><br>';

echo elgg_view('input/form', array('id' => 'timeline-settings-form', 'class' => 'elgg-form-settings', 'body' => $form));


/******************************************************/  

?>







<p>
	<?php echo elgg_echo('timelinestyle:settings:options');?>
	<p>
		<select name="params[colorsCustomizable]">
			<option value="yes" <?php if ($vars['entity']->colorsCustomizable == 'yes' || empty($vars['entity']->colorsCustomizable)) echo " selected=\"yes\" "; ?>><?php echo elgg_echo('option:yes'); ?></option>
			<option value="no" <?php if ($vars['entity']->colorsCustomizable == 'no') echo " selected=\"yes\" "; ?>><?php echo elgg_echo('option:no'); ?></option>
		</select>
		<?php echo elgg_echo("timelinestyle:menu:colors");?><br />
		
		<select name="params[timelineCustomizable]">
			<option value="yes" <?php if ($vars['entity']->timelineCustomizable == 'yes' || empty($vars['entity']->timelineCustomizable)) echo " selected=\"yes\" "; ?>><?php echo elgg_echo('option:yes'); ?></option>
			<option value="no" <?php if ($vars['entity']->timelineCustomizable == 'no') echo " selected=\"yes\" "; ?>><?php echo elgg_echo('option:no'); ?></option>
		</select>
		<?php echo elgg_echo("timelinestyle:menu:timeline");?><br /><br />
		
		<select name="params[showInRiver]">
			<option value="yes" <?php if ($vars['entity']->showInRiver == 'yes' || empty($vars['entity']->showInRiver)) echo " selected=\"yes\" "; ?>><?php echo elgg_echo('option:yes'); ?></option>
			<option value="no" <?php if ($vars['entity']->showInRiver == 'no') echo " selected=\"yes\" "; ?>><?php echo elgg_echo('option:no'); ?></option>
		</select>
		<?php echo elgg_echo("timelinestyle:settings:river");?><br>
		
		<select name="params[allowUploadtimeline]">
			<option value="yes" <?php if ($vars['entity']->allowUploadtimeline == 'yes' || empty($vars['entity']->allowUploadtimeline)) echo " selected=\"yes\" "; ?>><?php echo elgg_echo('option:yes'); ?></option>
			<option value="no" <?php if ($vars['entity']->allowUploadtimeline == 'no') echo " selected=\"yes\" "; ?>><?php echo elgg_echo('option:no'); ?></option>
		</select>
		<?php echo elgg_echo("timelinestyle:settings:upload");?><br>
		<?php if(!$vars['entity']->maxUploadSize) $vars['entity']->maxUploadSize = "512000";?>
		<input type="text" name="params[maxUploadSize]" value="<?php echo $vars['entity']->maxUploadSize;?>"/><?php echo elgg_echo("timelinestyle:settings:maxupload"); ?>
		
	</p>
</p>


<?php	
		$plugin = elgg_extract("entity", $vars); // group timeline layout
	
	
	echo "<div>";
	echo elgg_echo("group_timeline_style:settings:metadata_key");
	if(!$vars['entity']->metadata_key) $vars['entity']->metadata_key = "type";
	echo elgg_view("input/text", array("name" => "params[metadata_key]", "value" => $plugin->metadata_key));
	echo "</div>";
	
	echo "<div>";
	echo elgg_echo("group_timeline_style:settings:metadata_value");
	if(!$vars['entity']->metadata_value) $vars['entity']->metadata_value = "group";
	echo elgg_view("input/text", array("name" => "params[metadata_value]", "value" => $plugin->metadata_value));
	echo "</div>";

?>
