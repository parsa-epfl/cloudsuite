<?php
/**
 * ElggChat - Pure Elgg-based chat/IM
 *
 * Admin settings definition
 *
 * @package elggchat
 * @author ColdTrick IT Solutions
 * @copyright Coldtrick IT Solutions 2009-2014
 * @link http://www.coldtrick.com/
 *
 * for Elgg 1.8 and newer by iionly (iionly@gmx.de)
 * @copyright iionly 2014
 * @link https://github.com/iionly
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 */

$chatUpdateInterval = $vars['entity']->chatUpdateInterval;
$maxChatUpdateInterval = $vars['entity']->maxChatUpdateInterval;
$monitorUpdateInterval = $vars['entity']->monitorUpdateInterval;
$maxSessionAge = $vars['entity']->maxSessionAge;
$keepsessions = $vars['entity']->keepsessions;
$enableSounds = $vars['entity']->enableSounds;
$enableFlashing = $vars['entity']->enableFlashing;
$enableExtensions = $vars['entity']->enableExtensions;

if (empty($vars['entity']->onlinestatus_active)) {
	$vars['entity']->onlinestatus_active = 60;
}
if (empty($vars['entity']->onlinestatus_inactive)) {
	$vars['entity']->onlinestatus_inactive = 600;
}

?>

<p>
<select name="params[chatUpdateInterval]">
	<option value="5" <?php if ($chatUpdateInterval == 5 || empty($chatUpdateInterval)) echo " selected=\"yes\" ";  ?>>5</option>
	<option value="10" <?php if ($chatUpdateInterval == 10) echo " selected=\"yes\" "; ?>>10</option>
	<option value="15" <?php if ($chatUpdateInterval == 15) echo " selected=\"yes\" "; ?>>15</option>
	<option value="30" <?php if ($chatUpdateInterval == 30) echo " selected=\"yes\" "; ?>>30</option>
</select>
<?php echo elgg_echo('elggchat:admin:settings:chatupdateinterval'); ?><br />

<select name="params[maxChatUpdateInterval]">
	<option value="15" <?php if ($maxChatUpdateInterval == 15) echo " selected=\"yes\" "; ?>>15</option>
	<option value="30" <?php if ($maxChatUpdateInterval == 30 || empty($maxChatUpdateInterval)) echo " selected=\"yes\" "; ?>>30</option>
	<option value="45" <?php if ($maxChatUpdateInterval == 45) echo " selected=\"yes\" "; ?>>45</option>
	<option value="60" <?php if ($maxChatUpdateInterval == 60) echo " selected=\"yes\" "; ?>>60</option>
	<option value="120" <?php if ($maxChatUpdateInterval == 120) echo " selected=\"yes\" "; ?>>120</option>
</select>
<?php echo elgg_echo('elggchat:admin:settings:maxchatupdateinterval'); ?><br />

<select name="params[maxSessionAge]">
	<option value="3600" <?php if ($maxSessionAge == 3600) echo " selected=\"yes\" "; ?>><?php echo elgg_echo("elggchat:admin:settings:hour", array(1)); ?></option>
	<option value="21600" <?php if ($maxSessionAge == 21600 || empty($maxSessionAge)) echo " selected=\"yes\" "; ?>><?php echo elgg_echo("elggchat:admin:settings:hours", array(6)); ?></option>
	<option value="43200" <?php if ($maxSessionAge == 43200) echo " selected=\"yes\" "; ?>><?php echo elgg_echo("elggchat:admin:settings:hours", array(12)); ?></option>
	<option value="86400" <?php if ($maxSessionAge == 86400) echo " selected=\"yes\" "; ?>><?php echo elgg_echo("elggchat:admin:settings:hours", array(24)); ?></option>
	<option value="604800" <?php if ($maxSessionAge == 604800) echo " selected=\"yes\" "; ?>><?php echo elgg_echo("elggchat:admin:settings:days", array(7)); ?></option>
</select>
<?php echo elgg_echo('elggchat:admin:settings:maxsessionage'); ?><br />

<select name="params[keepsessions]">
	<option value="yes" <?php if ($keepsessions == "yes") echo " selected=\"yes\" "; ?>><?php echo elgg_echo("option:yes");?></option>
	<option value="no" <?php if ($keepsessions != "yes") echo " selected=\"yes\" "; ?>><?php echo elgg_echo("option:no");?></option>
</select>
<?php echo elgg_echo('elggchat:admin:settings:keepsessions'); ?><br />

<br>
<?php echo elgg_echo('elggchat:admin:settings:online_status:active') . elgg_view("input/text", array("name"=>"params[onlinestatus_active]", "value"=>$vars['entity']->onlinestatus_active));?>

<br>
<?php echo elgg_echo('elggchat:admin:settings:online_status:inactive') . elgg_view("input/text", array("name"=>"params[onlinestatus_inactive]", "value"=>$vars['entity']->onlinestatus_inactive));?>

<br>
<select name="params[enableSounds]">
	<option value="yes" <?php if ($enableSounds == "yes") echo " selected=\"yes\" "; ?>><?php echo elgg_echo("option:yes");?></option>
	<option value="no" <?php if ($enableSounds != "yes") echo " selected=\"yes\" "; ?>><?php echo elgg_echo("option:no");?></option>
</select>
<?php echo elgg_echo('elggchat:admin:settings:enable_sounds'); ?><br />

<select name="params[enableFlashing]">
	<option value="yes" <?php if ($enableFlashing == "yes") echo " selected=\"yes\" "; ?>><?php echo elgg_echo("option:yes");?></option>
	<option value="no" <?php if ($enableFlashing != "yes") echo " selected=\"yes\" "; ?>><?php echo elgg_echo("option:no");?></option>
</select>
<?php echo elgg_echo('elggchat:admin:settings:enable_flashing'); ?><br />

<select name="params[enableExtensions]">
	<option value="yes" <?php if ($enableExtensions == "yes") echo " selected=\"yes\" "; ?>><?php echo elgg_echo("option:yes");?></option>
	<option value="no" <?php if ($enableExtensions != "yes") echo " selected=\"yes\" "; ?>><?php echo elgg_echo("option:no");?></option>
	</select>
<?php echo elgg_echo('elggchat:admin:settings:enable_extensions'); ?><br />
</p>
