<?php
/**
 * ElggChat - Pure Elgg-based chat/IM
 *
 * Action to post a message in a chat session
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
	$chat_message = nl2br(get_input("chatmessage"));

	if (!empty($chat_message)) {
		$session = get_entity($sessionId);

		$session->annotate(ELGGCHAT_MESSAGE, $chat_message, ACCESS_LOGGED_IN, $userId);
		$session->save();
	}
}
exit();
?>