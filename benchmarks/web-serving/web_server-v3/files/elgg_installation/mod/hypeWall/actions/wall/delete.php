<?php

namespace hypeJunction\Wall;

$guid = get_input('guid');
$entity = get_entity($guid);

if (elgg_instanceof($entity, 'object', 'hjwall') && $entity->canEdit() && $entity->delete(true)) {
	system_message(elgg_echo('wall:delete:success'));
} else {
	register_error(elgg_echo('wall:delete:error'));
}

forward(REFERER);
