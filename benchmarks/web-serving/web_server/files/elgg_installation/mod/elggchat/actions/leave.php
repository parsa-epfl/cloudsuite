<?php
/**
 * ElggChat - Pure Elgg-based chat/IM
 *
 * Action to leave the specified session, and remove it from the system if no more members
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

$sessionId = (int) get_input("chatsession");
$userId = elgg_get_logged_in_user_guid();

if (check_entity_relationship($sessionId, ELGGCHAT_MEMBER, $userId)) {
	$session = get_entity($sessionId);
	$user = get_user($userId);

	remove_entity_relationship($sessionId, ELGGCHAT_MEMBER, $userId);

	$session->annotate(ELGGCHAT_SYSTEM_MESSAGE, elgg_echo('elggchat:action:leave', array($user->name)), ACCESS_LOGGED_IN, $userId);
	$session->save();

	// Clean up
	if ($session->countEntitiesFromRelationship(ELGGCHAT_MEMBER) == 0) {
		// No more members
		$keepsessions = elgg_get_plugin_setting("keepsessions","elggchat");
		if (elgg_get_plugin_setting("keepsessions","elggchat") != "yes") {
			$session->delete();
		}
	} elseif ($session->countAnnotations(ELGGCHAT_MESSAGE) == 0 && !check_entity_relationship($session->guid, ELGGCHAT_MEMBER, $session->owner_guid)) {
		// Owner left without leaving a real message
		$session->delete();
	}
}
exit();
?>