<?php
/**
 * ElggChat - Pure Elgg-based chat/IM
 *
 * Action to invite the specified user to an existing session
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

$inviteId = (int) get_input("friend");
$sessionId = (int) get_input("chatsession");
$userId =  elgg_get_logged_in_user_guid();

if (($invite_user = get_user($inviteId)) && ($session = get_entity($sessionId)) && $inviteId != $userId) {
	if ($session->getSubtype() == ELGGCHAT_SESSION_SUBTYPE && !check_entity_relationship($sessionId, ELGGCHAT_MEMBER, $inviteId) && check_entity_relationship($sessionId, ELGGCHAT_MEMBER, $userId)) {
		$session->addRelationship($inviteId, ELGGCHAT_MEMBER);
		$user = get_user($userId);

		$session->annotate(ELGGCHAT_SYSTEM_MESSAGE, elgg_echo('elggchat:action:invite', array($user->name, $invite_user->name)), ACCESS_LOGGED_IN, $userId);
		$session->save();
	}
}
exit();
?>