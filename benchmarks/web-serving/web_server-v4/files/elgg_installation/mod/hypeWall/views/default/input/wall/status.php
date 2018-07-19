<?php

namespace hypeJunction\Wall;

$input_type = elgg_get_plugin_setting('status_input_type', PLUGIN_ID);
if (!$input_type) {
	$input_type = 'plaintext';
}

if (!$vars['value'] && elgg_instanceof($vars['entity'])) {
	$vars['value'] = $vars['entity']->description;
}

$vars['class'] = "{$vars['class']} wall-input-status-wire";
$char_limit = (int) elgg_get_plugin_setting('character_limit', PLUGIN_ID);
if ($char_limit > 0) {
	$vars['data-limit'] = $char_limit;
	$counter = '<div class="wall-status-counter" data-counter><span data-counter-indicator class="wall-chars-counter">' . $char_limit . '</span>' . elgg_echo('wall:characters_remaining') . '</div>';
}

if (!isset($vars['name'])) {
	$vars['name'] = 'status';
}

echo $counter;
echo elgg_view("input/$input_type", $vars);
