<?php
/**
 * ElggChat - Pure Elgg-based chat/IM
 *
 * Definition of the user settings
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

$plugin = $vars["entity"];
$enable_chat = $plugin->getUserSetting("enableChat", elgg_get_page_owner_guid());
$allow_contact_from = $plugin->getUserSetting("allow_contact_from", elgg_get_page_owner_guid());
$show_offline_user = $plugin->getUserSetting("show_offline_user", elgg_get_page_owner_guid());

?>

<p>
<?php echo elgg_echo("elggchat:usersettings:enable_chat"); ?>
<select name="params[enableChat]">
	<option value="yes" <?php if($enable_chat == "yes" || empty($enable_chat)) echo "selected='yes'"; ?>><?php echo elgg_echo("option:yes"); ?></option>
	<option value="no"<?php if($enable_chat == "no") echo "selected='yes'"; ?>><?php echo elgg_echo("option:no"); ?></option>
</select>

<br>

<?php echo elgg_echo("elggchat:usersettings:allow_contact_from"); ?>
<select name="params[allow_contact_from]">
	<option value="all" <?php if($allow_contact_from == "all") echo "selected='yes'"; ?>><?php echo elgg_echo("elggchat:usersettings:allow_contact_from:all"); ?></option>
	<option value="friends"<?php if($allow_contact_from == "friends" || empty($enable_chat)) echo "selected='yes'"; ?>><?php echo elgg_echo("elggchat:usersettings:allow_contact_from:friends"); ?></option>
	<option value="none"<?php if($allow_contact_from == "none") echo "selected='yes'"; ?>><?php echo elgg_echo("elggchat:usersettings:allow_contact_from:none"); ?></option>
</select>

<br>

<?php echo elgg_echo("elggchat:usersettings:show_offline_user"); ?>
<select name="params[show_offline_user]">
	<option value="yes" <?php if($show_offline_user== "yes") echo "selected='yes'"; ?>><?php echo elgg_echo("option:yes"); ?></option>
	<option value="no"<?php if($show_offline_user== "no" || empty($show_offline_user)) echo "selected='yes'"; ?>><?php echo elgg_echo("option:no"); ?></option>
</select>
</p>
