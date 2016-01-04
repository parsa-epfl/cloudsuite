<?php

namespace hypeJunction\Wall;

if (isset($vars['class'])) {
	$vars['class'] = "{$vars['class']} wall-url";
} else {
	$vars['class'] = 'wall-url';
}

echo '<label>' . elgg_echo('wall:url') . '</label>';
echo elgg_view('input/url', $vars);