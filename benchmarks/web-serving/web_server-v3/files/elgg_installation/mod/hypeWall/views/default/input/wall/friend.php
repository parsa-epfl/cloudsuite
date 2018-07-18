<?php

namespace hypeJunction\Wall;

if (!elgg_view_exists('input/tokeninput')) {
	return;
}

if (!$vars['value'] && elgg_instanceof($vars['entity'])) {
	$vars['value'] = elgg_get_entities_from_relationship(array(
		'relationship' => 'tagged_in',
		'relationship_guid' => $vars['entity']->guid,
		'inverse_relationship' => true,
		'limit' => false
	));
}

$vars['callback'] = 'elgg_tokeninput_search_friends';

$vars['class'] = 'wall-tag-tokeninput';

if (!isset($vars['multiple'])) {
	$vars['multiple'] = true;
}

if (!isset($vars['strict'])) {
	$vars['strict'] = true;
}

echo '<label>' . elgg_echo('wall:tag_friends') . '</label>';
echo elgg_view('input/tokeninput', $vars);