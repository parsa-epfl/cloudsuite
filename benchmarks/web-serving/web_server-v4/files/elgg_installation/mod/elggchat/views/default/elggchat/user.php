<?php
/**
 * ElggChat - Pure Elgg-based chat/IM
 *
 * Nice display of an User for display in Friendspicker and Chat Members
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

$user = $vars["chatuser"];

if (!empty($user) && $user instanceof ElggUser) {
	$link = $vars["link"];
	$icon = $vars["icon"];
	$iconSize = $vars["iconSize"];
	$onlineStatus = $vars["onlineStatus"];

	if ($link !== false || $link !== true) {
		$link = true;
	}

	if ($icon !== false || $icon !== true) {
		$icon = true;
	}

	if ($onlineStatus !== false || $onlineStatus !== true) {
		$onlineStatus = true;
	}

	if (empty($iconSize) || !in_array($iconSize, array("tiny", "small", "medium", "large", "profile"))) {
		$iconSize = "tiny";
	}

	$result = "";
	$result .= "<tr class='chatmember'>";

	if ($icon) {
		$result .= "<td>".elgg_view('output/img', array('src' => elgg_format_url($user->getIconURL($iconSize)), 'class' => 'messageIcon'))."</td>";
	}

	if ($link) {
		$result .= "<td class='chatmemberinfo'><a href='" . $user->getUrl() . "' title='" . $user->name . "' rel='" . $user->guid . "'>" . $user->name . "</a></td>";
	} else {
		$result .= "<td class='chatmemberinfo'>". $user->name . "</td>";
	}

	if ($onlineStatus) {
		$diff = time() - $user->last_action;

		$inactive = (int) elgg_get_plugin_setting("onlinestatus_inactive", "elggchat");
		$active = (int) elgg_get_plugin_setting("onlinestatus_active", "elggchat");

		$title = elgg_echo("elggchat:session:onlinestatus", array(elgg_get_friendly_time($user->last_action)));

		if ($diff <= $active) {
			$result .= "<td><div class='online_status_chat' title='" . $title . "'></div></td>";
		} elseif ($diff <= $inactive) {
			$result .= "<td><div class='online_status_chat online_status_idle' title='" . $title . "'></div></td>";
		} else {
			$result .= "<td><div class='online_status_chat online_status_inactive' title='" . $title . "'></div></td>";
		}
	}

	$result .= "</tr>";

	echo $result;
}