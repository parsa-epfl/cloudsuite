<?php

namespace hypeJunction\Wall;

if (!$vars['value'] && elgg_instanceof($vars['entity'])) {
	$vars['value'] = elgg_get_entities_from_relationship(array(
		'relationship' => 'attached',
		'relationship_guid' => $vars['entity']->guid,
		'inverse_relationship' => true,
		'limit' => false
	));
}

$vars['callback'] = 'elgg_tokeninput_search_owned_entities';
$vars['data-results-limit'] = 10;

$vars['class'] = 'wall-attachment-tokeninput';

if (!isset($vars['multiple'])) {
	$vars['multiple'] = true;
}

if (!isset($vars['strict'])) {
	$vars['strict'] = true;
}

echo '<label>' . elgg_echo('wall:attachment') . '</label>';
echo elgg_view('input/tokeninput', $vars);