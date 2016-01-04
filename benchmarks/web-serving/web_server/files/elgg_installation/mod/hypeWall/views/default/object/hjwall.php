<?php

namespace hypeJunction\Wall;

elgg_load_css('wall');
elgg_load_css('fonts.font-awesome');
elgg_load_css('fonts.open-sans');

$entity = elgg_extract('entity', $vars);
$poster = $entity->getOwnerEntity();
$wall_owner = $entity->getContainerEntity();

$message = format_wall_message($entity);

if ($wall_owner->guid !== $poster->guid && $poster->guid !== elgg_get_page_owner_guid() && $wall_owner->guid !== elgg_get_page_owner_guid()) {
	$by = elgg_view('output/url', array(
		'text' => $poster->name,
		'href' => $poster->getURL()
	));
	$on = elgg_view('output/url', array(
		'text' => $wall_owner->name,
		'href' => $wall_owner->getURL()
	));
	$summary = elgg_echo('wall:new:wall:post', array($by, $on));
} else {
	$author_link = elgg_view('output/url', array(
		'text' => $poster->name,
		'href' => $poster->getURL(),
	));
	$message = "$author_link: $message";
}


if ($entity->address) {
	$att_str = elgg_view('output/wall/url', array(
		'value' => $entity->address,
	));
}
$att_str .= $entity->html;
$attachments = elgg_get_entities_from_relationship(array(
	'relationship' => 'attached',
	'relationship_guid' => $entity->guid,
	'inverse_relationship' => true,
	'limit' => false,
		));
if ($attachments) {
	if (count($attachments) > 1) {
		$att_str .= elgg_view_entity_list($attachments, array(
			'list_type' => elgg_in_context('widgets') ? 'list' : 'gallery',
			'full_view' => false,
			'size' => 'small'
		));
	} else {
		foreach ($attachments as $attachment) {
			$att_str .= elgg_view('output/wall/attachment', array(
				'entity' => $attachment
			));
		}
	}
}

$att_str = '<div class="wall-attachments">' . $att_str . '</div>';

$menu = elgg_view_menu('entity', array(
	'entity' => $entity,
	'handler' => 'wall',
	'sort_by' => 'priority',
	'class' => 'elgg-menu-hz',
		));

if (elgg_in_context('thewire')) {
	$metadata = $menu;
	$menu = '';
}

if (elgg_in_context('widgets')) {
	$menu = $metadata = '';
	$subtitle = elgg_echo('byline', array($poster->name)) . ' ' . elgg_view_friendly_time($entity->time_created);
}

if (elgg_extract('full_view', $vars, false)) {
	$comments = elgg_view_comments($entity);
}

$params = array(
	'entity' => $entity,
	'title' => (!empty($summary)) ? $summary : false,
	'metadata' => $metadata,
	'tags' => false,
	'subtitle' => $subtitle,
	'content' => $message . $att_str . $menu . $comments,
);

$params = $params + $vars;
$content = '<div class="wall-bubble">' . elgg_view('object/elements/summary', $params) . '</div>';

$user_icon = elgg_view_entity_icon($poster, 'medium', array(
	'use_hover' => false,
	'img_class' => 'wall-poster-avatar'
		));

if (!elgg_in_context('widgets')) {
	if (elgg_in_context('wall') && $poster->guid == elgg_get_page_owner_guid()) {
		echo elgg_view_image_block('', $content, array(
			'image_alt' => $user_icon,
			'class' => 'wall-post-alt'
		));
	} else {
		echo elgg_view_image_block($user_icon, $content, array('class' => 'wall-post'));
	}
} else {
	echo $content;
}