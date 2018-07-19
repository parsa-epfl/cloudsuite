<?php
/**
 * ElggChat - Pure Elgg-based chat/IM
 *
 * Action to create a chat session with specified user
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

$inviteId = (int) get_input("invite");

$user = elgg_get_logged_in_user_entity();

if (($invite_user = get_user($inviteId)) && $inviteId != $user->guid) {

	$session = new ElggObject();
	$session->subtype = ELGGCHAT_SESSION_SUBTYPE;
	$session->access_id = ACCESS_LOGGED_IN;
	$session->setMetaData("tag","");
	$session->save();

	$session->addRelationship($user->guid, ELGGCHAT_MEMBER);
	$session->addRelationship($invite_user->guid, ELGGCHAT_MEMBER);

	echo $session->guid;
}
exit();
?>