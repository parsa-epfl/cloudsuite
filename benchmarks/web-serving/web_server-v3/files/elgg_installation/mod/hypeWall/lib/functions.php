<?php

namespace hypeJunction\Wall;

use ElggBatch;
use ElggObject;
use hypeJunction\Util\Extractor;

/**
 * Callback function for token input search
 *
 * @param string $term
 * @param array $options
 * @return array
 */
function search_locations($term, $options = array()) {

	$term = sanitize_string($term);

	$q = str_replace(array('_', '%'), array('\_', '\%'), $term);

	$options['metadata_names'] = array('location', 'temp_location');
	$options['group_by'] = "v.string";
	$options['wheres'] = array("v.string LIKE '%$q%'");

	return elgg_get_metadata($options);
}

/**
 * Get coordinates and location name of the current session
 * @return array
 */
function get_geopositioning() {
	if (isset($_SESSION['geopositioning'])) {
		return $_SESSION['geopositioning'];
	}
	return array(
		'location' => '',
		'latitude' => 0,
		'longitude' => 0
	);
}

/**
 * Set session geopositioning
 * Cache geocode along the way
 * 
 * @param string $location
 * @param float $latitude
 * @param float $longitude
 * @return void
 */
function set_geopositioning($location = '', $latitude = 0, $longitude = 0) {

	$location = sanitize_string($location);
	$lat = (float) $latitude;
	$long = (float) $longitude;

	$latlong = elgg_geocode_location($location);
	if ($latlong) {
		$latitude = elgg_extract('lat', $latlong);
		$longitude = elgg_extract('long', $latlong);
	} else if ($location && $latitude && $longitude) {
		$dbprefix = elgg_get_config('dbprefix');
		$query = "INSERT INTO {$dbprefix}geocode_cache
				(location, lat, `long`) VALUES ('$location', '{$lat}', '{$long}')
				ON DUPLICATE KEY UPDATE lat='{$lat}', `long`='{$long}'";

		insert_data($query);
	}

	$_SESSION['geopositioning'] = array(
		'location' => $location,
		'latitude' => (float) $latitude,
		'longitude' => (float) $longitude
	);
}

/**
 * Get a wall post message suitable for notifications and status updates
 * @param ElggObject $object
 * @param bool $include_address Include URL address in the message body
 * @return string
 */
function format_wall_message($object, $include_address = false) {

	$status = $object->description;
	$status = Extractor::render($status);

	$message = array(0 => $status);

	$tagged_friends = get_tagged_friends($object, 'links');
	if ($tagged_friends) {
		$message[2] = '<span class="wall-tagged-friends">' . elgg_echo('wall:with', array(implode(', ', $tagged_friends))) . '</span>';
	}

	$location = $object->getLocation();
	if ($location) {
		$location = elgg_view('output/wall/location', array('value' => $location));
		$message[3] = '<span class="wall-tagged-location">' . elgg_echo('wall:at', array($location)) . '</span>';
	}

	$attachments = get_attachments($object, 'links');
	if ($attachments) {
		$attachments_str = (count($attachments) == 1) ? elgg_echo('wall:attached:single') : elgg_echo('wall:attached', array(count($attachments)));
		$message[4] = '<span class="wall-tagged-attachments">' . $attachments_str . '</span>';
	}

	if (!$status || $include_address) {
		$address = $object->address;
		if ($address && (strpos($status, $address) === false)) {
			$message[1] = elgg_view('output/url', array(
				'href' => $address,
				'class' => 'wall-attached-url',
			));
		}
	}

	ksort($message);

	$output = implode(' ', $message);
	return elgg_trigger_plugin_hook('message:format', 'wall', array('entity' => $object), $output);
}

/**
 * Get tagged friends
 *
 * @param ElggObject $object
 * @param string $format	links|icons or null for an array of entities
 * @param size $size  Icon size
 * @return string
 */
function get_tagged_friends($object, $format = null, $size = 'small') {

	$tagged_friends = array();

	$tags = new ElggBatch('elgg_get_entities_from_relationship', array(
		'types' => 'user',
		'relationship' => 'tagged_in',
		'relationship_guid' => $object->guid,
		'inverse_relationship' => true,
		'limit' => false
	));

	foreach ($tags as $tag) {
		if ($format == 'links') {
			$tagged_friends[] = elgg_view('output/url', array(
				'text' => (isset($tag->name)) ? $tag->name : $tag->title,
				'href' => $tag->getURL(),
				'is_trusted' => true
			));
		} else if ($format == 'icons') {
			$tagged_friends[] = elgg_view_entity_icon($tag, $size, array(
				'class' => 'wall-post-tag-icon',
				'use_hover' => false
			));
		} else {
			$tagged_friends[] = $tag;
		}
	}

	return $tagged_friends;
}

/**
 * Get attachments
 *
 * @param ElggObject $object
 * @param string $format	links|icons or null for an array of entities
 * @param size $size  Icon size
 * @return string
 */
function get_attachments($object, $format = null, $size = 'small') {

	$attachment_tags = array();

	$attachments = new ElggBatch('elgg_get_entities_from_relationship', array(
		'relationship' => 'attached',
		'relationship_guid' => $object->guid,
		'inverse_relationship' => true,
		'limit' => false
	));

	foreach ($attachments as $attachment) {
		if ($format == 'links') {
			$attachment_tags[] = elgg_view('output/url', array(
				'text' => (isset($attachment->name)) ? $attachment->name : $attachment->title,
				'href' => $attachment->getURL(),
				'is_trusted' => true
			));
		} else if ($format == 'icons') {
			$attachment_tags[] = elgg_view_entity_icon($attachment, $size, array(
				'class' => 'wall-post-tag-icon',
				'use_hover' => false
			));
		} else {
			$attachment_tags[] = $attachment;
		}
	}

	return $attachment_tags;
}

/**
 * Extract hashtags from a text
 * @param string $text
 * @return array
 */
function get_hashtags($text) {
	$tags = array();
	preg_match_all('/(^|[^\w])#(\w*[^\s\d!-\/:-@]+\w*)/', $text, $tags);
	return $tags[2];
}
