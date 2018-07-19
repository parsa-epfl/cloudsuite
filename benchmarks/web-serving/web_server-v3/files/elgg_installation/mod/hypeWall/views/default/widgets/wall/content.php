<?php

elgg_load_css('wall');
elgg_load_css('fonts.font-awesome');
elgg_load_css('fonts.open-sans');

elgg_push_context('wall');

if ($vars['entity']->show_add_form) {
	$content = elgg_view("framework/wall/container");
}

$owner = elgg_get_page_owner_entity();
if (!$owner) {
	if (!elgg_is_logged_in()) {
		return false;
	}
	$owner = elgg_get_logged_in_user_entity();
}

$dbprefix = elgg_get_config('dbprefix');
$content .= elgg_list_entities(array(
	'types' => 'object',
	'subtypes' => array('hjwall', 'thewire'),
	'joins' => array(
		"JOIN {$dbprefix}entity_relationships r ON r.guid_one = $owner->guid",
	),
	'wheres' => array(
		"(e.owner_guid = $owner->guid OR e.container_guid = $owner->guid OR (r.guid_two = e.guid AND r.relationship = 'tagged_in'))"
	),
	'list_class' => 'wall-widget-list',
	'full_view' => false,
	'limit' => $vars['entity']->num_display,
	'pagination' => false,
));

elgg_pop_context();

echo $content;

$wall_url = "wall/owner/" . elgg_get_page_owner_entity()->username;
$wall_link = elgg_view('output/url', array(
	'href' => $wall_url,
	'text' => elgg_echo('wall:moreposts'),
	'is_trusted' => true,
		));
echo "<span class=\"elgg-widget-more\">$wall_link</span>";
