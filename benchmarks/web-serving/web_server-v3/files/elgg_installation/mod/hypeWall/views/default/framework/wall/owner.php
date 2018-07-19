<?php

$owner = elgg_get_page_owner_entity();

if (!$owner) {
	return;
}

$dbprefix = elgg_get_config('dbprefix');
$content = elgg_list_entities(array(
	'types' => 'object',
	'subtypes' => array('hjwall', 'thewire'),
	'joins' => array(
		"JOIN {$dbprefix}entity_relationships r ON r.guid_one = $owner->guid",
	),
	'wheres' => array(
		"(e.owner_guid = $owner->guid OR e.container_guid = $owner->guid OR (r.guid_two = e.guid AND r.relationship = 'tagged_in'))"
	),
	'list_class' => 'wall-post-list',
	'full_view' => true,
	'limit' => elgg_extract('limit', $vars, 10),
		));

if (!$content) {
	echo '<p>' . elgg_echo('wall:empty') . '</p>';
} else {
	echo $content;
}

