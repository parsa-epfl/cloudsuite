<?php
/**
 * ElggChat - Pure Elgg-based chat/IM
 *
 * Action to get all the information to form the ElggChat Toolbar
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

if ($user = elgg_get_logged_in_user_entity()) {

	$chat_sessions_count = elgg_get_entities_from_relationship(array('relationship' => ELGGCHAT_MEMBER,
		'relationship_guid' => $user->getGUID(),
		'inverse_relationship' => true,
		'order_by' => "time_created desc",
		'limit' => false,
		'count' => true,
	));

	$result = array();

	if ($chat_sessions_count > 0) {

		// Generate sessions
		$chat_sessions = $user->getEntitiesFromRelationship(array('relationship' => ELGGCHAT_MEMBER, 'inverse_relationship' => true));
		if (!empty($chat_sessions)) {

			krsort($chat_sessions);

			$result["sessions"] = array();

			foreach($chat_sessions as $session) {
				// Dont show session if not mine and no (non system) messages
				if (!(($session->owner_guid != $user->guid) && ($session->countAnnotations(ELGGCHAT_MESSAGE) == 0))) {
					$result["sessions"][$session->guid] = array();

					// List all the Members of the chat session
                    $members = $session->getEntitiesFromRelationship(array('relationship' => ELGGCHAT_MEMBER));
					if (is_array($members) && count($members) > 1) {

						$result["sessions"][$session->guid]["members"] = array();

						$firstMember = true;

						foreach($members as $member) {
							if ($member->guid != $user->guid) {
								if ($firstMember) {
									if (count($members) > 2) {
										$result["sessions"][$session->guid]["name"] = $member->name . " [" . (count($members) - 2) . "]";
									} else {
										$result["sessions"][$session->guid]["name"] = $member->name;
									}
									$firstMember = false;
								}
								$result["sessions"][$session->guid]["members"][] = elgg_view("elggchat/user", array("chatuser" => $member));
							}
						}
					} else {
						$result["sessions"][$session->guid]["name"] = elgg_echo("elggchat:session:name:default", array($session->guid));
					}

					// List all the messages in the session
					$msg_count = elgg_get_annotations(array(
						'annotation_names' => array(ELGGCHAT_MESSAGE, ELGGCHAT_SYSTEM_MESSAGE),
						'guid' => $session->guid,
						'count' => true,
					));

					$result["sessions"][$session->guid]["message_count"] = $msg_count;

					if ($msg_count > 0) {
						$annotations = elgg_get_annotations(array(
							'annotation_names' => array(ELGGCHAT_MESSAGE, ELGGCHAT_SYSTEM_MESSAGE),
							'guid' => $session->guid,
							'limit' => $msg_count,
							'order' => 'desc',
						));
						$result["sessions"][$session->guid]["messages"] = array();

						foreach($annotations as $msg) {
							$result["sessions"][$session->guid]["messages"][$msg->id] = elgg_view("elggchat/message", array("message" => $msg, "message_owner" => get_user($msg->owner_guid), "offset" => $msg->id));
						}
					}
				}
			}
		}
	}

	$result["friends"] = array();
	$result["friends"]["offline"] = array();
	$result["friends"]["online"] = array();

	// Add friends information
	$friends_count = elgg_get_entities_from_relationship(array(
		'relationship' => 'friend',
		'relationship_guid' => $user->getGUID(),
		'inverse_relationship' => true,
		'type' => 'user',
		'count' => true,
	));
	if ($friends_count > 0) {
		$friends = elgg_get_entities_from_relationship(array(
			'relationship' => 'friend',
			'relationship_guid' => $user->getGUID(),
			'inverse_relationship' => true,
			'type' => 'user',
			'limit' => $friends_count,
		));

		$inactive = (int) elgg_get_plugin_setting("onlinestatus_inactive", "elggchat");
		$time = time();
		foreach ($friends as $friend) {
			if ($time - $friend->last_action <= $inactive) {
				$result["friends"]["online"][] = elgg_view("elggchat/user", array("chatuser" => $friend));
			} else {
				$result["friends"]["offline"][] = elgg_view("elggchat/user", array("chatuser" => $friend));
			}
		}
	}

	// Prepare to send nice JSON
	header("Content-Type: application/json; charset=UTF-8");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");

	echo json_encode($result);
}
exit();
?>