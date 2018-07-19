<?php

/**
 * Wall post tag river item
 */

namespace hypeJunction\Wall;

elgg_push_context('wall');

// River access level will vary from that of the original post
$ia = elgg_set_ignore_access(true);
$tagged_user = $vars['item']->getSubjectEntity();
$wall_post = $vars['item']->getObjectEntity();
$poster = $wall_post->getOwnerEntity();

$tagged_user_link = elgg_view('output/url', array(
	'text' => $tagged_user->name,
	'href' => $tagged_user->getURL()
		));

$poster_link = elgg_view('output/url', array(
	'text' => $poster->name,
	'href' => $poster->getURL()
		));
$wall_post_link = elgg_view('output/url', array(
	'text' => elgg_echo('wall:tag:river:post'),
	'href' => $wall_post->getURL(),
));

$summary = elgg_echo('wall:tag:river', array($poster_link, $tagged_user_link, $wall_post_link));

$attachment = format_wall_message($wall_post, true);

elgg_set_ignore_access($ia);

echo elgg_view('river/item', array(
	'item' => $vars['item'],
	'summary' => $summary,
	'message' => $message,
	'attachments' => $attachment
));

elgg_pop_context();
