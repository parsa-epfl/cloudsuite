<?php

namespace hypeJunction\Wall;

$subject = $vars['item']->getSubjectEntity();
$object = $vars['item']->getObjectEntity();
$wall_owner = $object->getContainerEntity();

$message = format_wall_message($object);

if ($wall_owner->guid !== $subject->guid && $wall_owner->guid !== elgg_get_page_owner_guid()) {
	$by = elgg_view('output/url', array(
		'text' => $subject->name,
		'href' => $subject->getURL()
	));
	$on = elgg_view('output/url', array(
		'text' => $wall_owner->name,
		'href' => $wall_owner->getURL()
	));
	$summary = elgg_echo('wall:new:wall:post', array($by, $on));
} else {
	$author_link = elgg_view('output/url', array(
		'text' => $subject->name,
		'href' => $subject->getURL(),
	));
	$message = "$author_link: $message";
}

if (!$summary) {
	$summary = $message;
	$message = false;
}

if ($object->address) {
	$att_str = elgg_view('output/wall/url', array(
		'value' => $object->address,
	));
}
$att_str .= $object->html;

$attachments = get_attachments($object);
if ($attachments) {
	if (count($attachments) > 0) {
		$att_str .= elgg_view_entity_list($attachments, array(
			'list_type' => elgg_in_context('widgets') ? 'list' : 'gallery',
			'full_view' => false,
			'icon_size' => 'large'
		));
	} else {
		foreach ($attachments as $attachment) {
			$att_str .= elgg_view('output/wall/attachment', array(
				'entity' => $attachment
			));
		}
	}
}

echo elgg_view('river/item', array(
	'item' => $vars['item'],
	'summary' => $summary,
	'message' => $message,
	'attachments' => $att_str
));
