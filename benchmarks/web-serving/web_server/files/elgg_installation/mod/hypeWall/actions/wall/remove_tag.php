<?php

namespace hypeJunction\Wall;

use ElggRelationship;

$guid = get_input('guid');
$entity = get_entity($guid);
$user = elgg_get_logged_in_user_entity();

if (elgg_instanceof($entity) && ($relationship = check_entity_relationship($user->guid, 'tagged_in', $entity->guid))) {
	if ($relationship instanceof ElggRelationship) {
		if ($relationship->delete()) {
			elgg_delete_river(array(
				'subject_guids' => $user->guid,
				'object_guids' => $entity->guid,
				'action_types' => 'tagged',
			));

			/**
			 * @todo: remove from access collection?
			 */
			
			system_message(elgg_echo('wall:remove_tag:success'));
			forward(REFERER);
		}
	}
}

register_error(elgg_echo('wall:remove_tag:error'));
forward(REFERER);
