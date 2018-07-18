<?php

namespace hypeJunction\Wall;

/**
 * Listen to the 'publish','object' event and send out notifications
 * to interested users, as well as anyone tagged
 *
 * @param string $event			Equals 'publish'
 * @param string $entity_type	Equals 'object'
 * @param ElggEntity $entity	Published entity
 */
function send_custom_notifications($event, $entity_type, $entity) {

	if ($entity->origin !== 'wall') {
		return true;
	}

	// We only want to notify about wire posts and wall posts, all content created therewith is implied
	$accepted_subtypes = array('hjwall', 'thewire');
	if (!in_array($entity->getSubtype(), $accepted_subtypes)) {
		return true;
	}

	$poster = $entity->getOwnerEntity();
	$container = $entity->getContainerEntity();
	$message = format_wall_message($entity, true);

	$sent = array(elgg_get_logged_in_user_guid(), $poster->guid, $container->guid);

	// Notify wall owner
	if ($poster->guid !== $container->guid && elgg_instanceof($container, 'user')) {
		$to = $container->guid;
		$from = $poster->guid;

		$target = elgg_echo("wall:target:{$entity->getSubtype()}");
		$ownership = elgg_echo('wall:ownership:your', array($target));

		$subject = elgg_echo('wall:new:notification:subject', array($poster->name, $ownership));
		$summary = elgg_view('output/url', array(
			'text' => $subject,
			'href' => $entity->getURL(),
		));
		$body = elgg_echo('wall:new:notification:message', array(
			$poster->name,
			$ownership,
			$message,
			$entity->getURL()
		));

		notify_user($to, $from, $subject, $body, array(
			'summary' => $summary,
			'object' => $entity,
			'action' => 'received',
		));
	}

	// Notify tagged users
	$tagged_friends = get_tagged_friends($entity);
	foreach ($tagged_friends as $tagged_friend) {
		// user tagged herself or the wall owner
		if ($tagged_friend->guid == $poster->guid || $tagged_friend->guid == $container->guid || in_array($tagged_friend->guid, $sent)) {
			continue;
		}

		$sent[] = $tagged_friend->guid;

		$to = $tagged_friend->guid;
		$from = $poster->guid;
		$subject = elgg_echo('wall:tagged:notification:subject', array($poster->name));
		$summary = elgg_view('output/url', array(
			'text' => $subject,
			'href' => $entity->getURL(),
		));
		$body = elgg_echo('wall:tagged:notification:message', array(
			$poster->name,
			$message,
			$entity->getURL()
		));

		notify_user($to, $from, $subject, $body, array(
			'summary' => $summary,
			'object' => $entity,
			'action' => 'tagged',
		));
	}

	return true;
}
