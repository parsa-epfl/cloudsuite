<?php
/**
 * ElggChat - Pure Elgg-based chat/IM
 *
 * Admin page to list all the chat sessions
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

$session_guid = (int)get_input('session_guid');

if($session_guid && ($session = get_entity($session_guid)) && ($session->getSubtype() == ELGGCHAT_SESSION_SUBTYPE)) {

	$back_button =  elgg_view("output/url", array(
		'href' => elgg_get_site_url() . "admin/administer_utilities/elggchat",
		'text' => elgg_echo('elggchat:sessions_backbutton'),
		'is_trusted' => true,
		'class' => 'elgg-button elgg-button-action'
	));

	$details = elgg_view_entity($session, array('full_view' => true));

	if (!$details) {
		$details = '<p class="mtm"><b>' . elgg_echo('elggchat:session:no_session_details') . '</b></p>';
	}
	echo $back_button."<br><br>";
	echo elgg_echo('elggchat:session_details_introduction')."<br>";
	echo $details;

} else {

	$offset = (int)get_input('offset', 0);
	$limit = 10;

	$list = elgg_list_entities(array(
		'type' => 'object',
		'subtype' => ELGGCHAT_SESSION_SUBTYPE,
		'limit' => $limit,
		'full_view' => false,
		'offset' => $offset
	));

	if (!$list) {
		$list = '<p class="mtm"><b>' . elgg_echo('elggchat:session:no_sessions') . '</b></p>';
	}
	echo elgg_echo('elggchat:session_list_introduction')."<br>";
	echo $list;
}