<?php

/**
 * Add tagged friends and embeddable content to wire posts
 */

namespace hypeJunction\Wall;

$entity = elgg_extract('entity', $vars);

$tagged_friends = get_tagged_friends($entity, 'links');
if ($tagged_friends) {
	echo '<span class="elgg-subtext wall-tagged-friends">' . elgg_echo('wall:with', array(implode(', ', $tagged_friends))) . '</span>';
}

$location = $entity->getLocation();
if ($location) {
	$location = elgg_view('output/wall/location', array('value' => $location));
	echo '<span class="elgg-subtext wall-tagged-location">' . elgg_echo('wall:at', array($location)) . '</span>';
}

if ($entity->address) {
	echo elgg_view('output/wall/url', array(
		'value' => $entity->address,
	));
}

echo $entity->html;

$attachments = get_attachments($entity);
if ($attachments) {
	if (count($attachments) > 0) {
		echo elgg_view_entity_list($attachments, array(
			'list_type' => 'gallery',
			'full_view' => false,
			'size' => 'medium'
		));
	} else {
		foreach ($attachments as $attachment) {
			echo elgg_view('output/wall/attachment', array(
				'entity' => $attachment
			));
		}
	}
}