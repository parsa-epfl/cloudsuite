<?php

/**
 * Form that allows users to update their status
 */

namespace hypeJunction\Wall;

$status = elgg_view('input/wall/status', array(
	'name' => 'status',
	'class' => 'wall-input-description',
	'placeholder' => elgg_echo('wall:status:placeholder')
		));

$attachment = elgg_view('input/wall/attachment', array(
	'name' => 'attachment_guids',
		));

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
	'value' => 'status',
		));

$hidden .= elgg_view('input/hidden', array(
	'name' => 'container_guid',
	'value' => elgg_extract('container_guid', $vars, elgg_get_page_owner_guid())
));

$html = <<<HTML
	<fieldset class="wall-fieldset-status">$status</fieldset>
	<fieldset class="wall-fieldset-attachment">
		<div class="wall-input-attachment">$attachment</div>
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
