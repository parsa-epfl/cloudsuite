<?php

/**
 * Form that allows users to update their status
 */

namespace hypeJunction\Wall;

$status = elgg_view('input/wall/status', array(
	'name' => 'status',
	'class' => 'wall-input-status',
	'placeholder' => elgg_echo('wall:status:placeholder')
		));

$container_guid = elgg_get_plugin_user_setting('wall_collection', 0, PLUGIN_ID);
if (!$container_guid) {
	$container_guid = elgg_get_page_owner_guid();
}

$files = elgg_view('input/wall/file', array(
	'container_guid' => $container_guid
		));

if (WALL_GEOPOSITIONING) {
	$location = '<div class="wall-input-tag-location">';
	$location .= elgg_view('input/wall/location', array(
		'name' => 'location',
		'data-hint-text' => elgg_echo('wall:tag:location:hint'),
	));
	$location .= '</div>';
}

if (WALL_TAG_FRIENDS) {
	$friends = '<div class="wall-input-tag-friends">';
	$friends .= elgg_view('input/wall/friend', array(
		'name' => 'friend_guids',
		'data-hint-text' => elgg_echo('wall:tag:friends:hint'),
	));
	$friends .= '</div>';
}

$access = elgg_view('input/access', array(
	'class' => 'wall-access',
	'name' => 'access_id'
		));

$button = elgg_view('input/submit', array(
	'value' => elgg_echo('wall:post'),
	'class' => 'elgg-button elgg-button-submit',
		));

$hidden .= elgg_view('input/hidden', array(
	'name' => 'origin',
	'value' => 'wall',
		));

$hidden .= elgg_view('input/container_guid', array(
	'name' => 'container_guid',
	'value' => $container_guid
		));

$hidden .= elgg_view('input/hidden', array(
	'name' => 'container_guid',
	'value' => elgg_extract('container_guid', $vars, elgg_get_page_owner_guid())
));

$html = <<<HTML
	<fieldset class="wall-fieldset-status">$status</fieldset>
	<fieldset class="wall-fieldset-attachment">
		<div class="wall-dropzone">$files</div>
	</fieldset>
	<fieldset class="wall-fieldset-tags">
		$location
		$friends
	</fieldset>
	<fieldset class="elgg-foot">
		<ul class="wall-bar-controls">
			<li>$access</li>
			<li>$button</li>
		</ul>
	</fieldset>
	$hidden
HTML;

echo $html;
