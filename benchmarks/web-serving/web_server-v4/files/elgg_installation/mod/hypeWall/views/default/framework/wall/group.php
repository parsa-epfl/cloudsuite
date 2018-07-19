<?php

$group = elgg_get_page_owner_entity();

if (!elgg_instanceof($group, 'group')) {
	return;
}

$content = elgg_list_entities(array(
	'types' => 'object',
	'subtypes' => array('hjwall'),
	'container_guids' => $group->guid,
	'list_class' => 'wall-post-list',
	'full_view' => true,
	'limit' => elgg_extract('limit', $vars, 10),
));

if (!$content) {
	echo '<p>' . elgg_echo('wall:empty') . '</p>';
} else {
	echo $content;
}

