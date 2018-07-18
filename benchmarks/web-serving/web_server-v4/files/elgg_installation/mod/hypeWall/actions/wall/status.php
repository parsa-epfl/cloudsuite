<?php

namespace hypeJunction\Wall;

use ElggObject;
use hypeJunction\Filestore\UploadHandler;
use hypeJunction\Util\Embedder;
use hypeJunction\Util\Extractor;

$poster = elgg_get_logged_in_user_entity();

$status = strip_tags(get_input('status'), '');
$location = get_input('location', '');

// GUIDs of friends that were tagged in the post
$friend_guids = get_input('friend_guids', '');
if (!is_array($friend_guids)) {
	$friend_guids = string_to_tag_array($friend_guids);
}

// GUIDs of entities that were tagged in the post
$attachment_guids = get_input('attachment_guids', '');
if (!is_array($attachment_guids)) {
	$attachment_guids = string_to_tag_array($attachment_guids);
}

// GUIDs of files that were uploaded during posting
$upload_guids = get_input('upload_guids', array());
if (!is_array($upload_guids)) {
	$upload_guids = array();
}

// URL Address
$address = get_input('address');

$access_id = get_input('access_id');
$container_guid = get_input('container_guid');
if ($container_guid) {
	$container = get_entity($container_guid);
	if (elgg_instanceof($container)) {
		if ($container->guid !== $poster->guid) {
			$subtype = 'hjwall';
		} else {
			$subtype = WALL_SUBTYPE;
		}
		if (!$container->canWriteToContainer($poster->guid, 'object', $subtype)) {
			register_error(elgg_echo('wall:error:container_permissions'));
			forward(REFERER);
		}
	}
}

if (!$container) {
	$container = $poster;
}

elgg_set_page_owner_guid($container->guid);

if (!$subtype) {
	$subtype = WALL_SUBTYPE;
}

if (!$status && !$address) {
	register_error(elgg_echo('wall:error:empty_form'));
	forward(REFERER);
}

if ($poster->guid == $container_guid) {
	$title = elgg_echo('wall:post:status_update', array(elgg_echo('wall:byline', array($poster->name))));
} else {
	$title = elgg_echo('wall:post:wall_to_wall', array(elgg_echo('wall:byline', array($poster->name))));
}

if ($subtype == 'thewire' && is_callable('thewire_save_post')) {
	$guid = thewire_save_post($status, $poster->guid, $access_id, 0, 'wall');
	$wall_post = get_entity($guid);
} else {
	$wall_post = new ElggObject();
	$wall_post->subtype = $subtype;
	$wall_post->access_id = $access_id;
	$wall_post->owner_guid = $poster->guid;
	$wall_post->container_guid = $container->guid;
	$wall_post->title = $title;
	$wall_post->description = $status;
	if ($guid = $wall_post->save()) {
		// Create a river entry for this wall post
		$river_id = elgg_create_river_item(array(
			'view' => 'river/object/hjwall/create',
			'action_type' => 'create',
			'subject_guid' => $wall_post->getOwnerGUID(),
			'object_guid' => $wall_post->getGUID(),
			'target_guid' => $wall_post->getContainerGUID(),
		));
	}
}

if ($guid && $wall_post) {

	$wall_post->origin = 'wall';

	// Wall post access id is set to private, which means it should be visible only to the poster and tagged users
	// Creating a new ACL for that
	if ($access_id == ACCESS_PRIVATE && count($friend_guids)) {

		$user_guids = array($poster->guid, $container->guid);
		$user_guids = array_merge($user_guids, $friend_guids);
		$user_guids = array_unique($user_guids);
		sort($user_guids);

		$acl_hash = sha1(implode(':', $user_guids));
		$dbprefix = elgg_get_config('dbprefix');
		$query = "SELECT * FROM {$dbprefix}access_collections WHERE name = '$acl_hash'";
		$collection = get_data_row($query);
		$acl_id = $collection->id;
		if (!$acl_id) {
			$site = elgg_get_site_entity();
			$acl_id = create_access_collection($acl_hash, $site->guid);
			update_access_collection($acl_id, $user_guids);
		}
		$wall_post->access_id = $acl_id;
		$wall_post->save();
	}

	$extractor = Extractor::extract($status);

	if (count($extractor->hashtags)) {
		$wall_post->tags = $extractor->hashtags;
	}

	if (count($extractor->usernames)) {
		foreach ($extractor->usernames as $username) {
			$user = get_user_by_username($username);
			if (elgg_instanceof($user) && !in_array($user->guid, $friend_guids)) {
				$friend_guids[] = $user->guid;
			}
		}
	}

	// Add 'tagged_in' relationships
	// If the access level for the post is not set to private, also create a river item with the access level specified in their settings by the tagged user
	if (count($friend_guids)) {
		foreach ($friend_guids as $friend_guid) {
			if (add_entity_relationship($friend_guid, 'tagged_in', $wall_post->guid)) {
				if (!in_array($access_id, array(ACCESS_PRIVATE, ACCESS_LOGGED_IN, ACCESS_PUBLIC))) {
					$river_access_id = elgg_get_plugin_user_setting('river_access_id', $friend_guid, PLUGIN_ID);
					if (!is_null($river_access_id) && $river_access_id !== ACCESS_PRIVATE) {
						$river_id = elgg_create_river_item(array(
							'view' => 'river/relationship/tagged/create',
							'action_type' => 'tagged',
							'subject_guid' => $friend_guid,
							'object_guid' => $wall_post->getGUID(),
							'target_guid' => $wall_post->getContainerGUID(),
							'access_id' => $river_access_id,
						));
					}
				}
			}
		}
	}

	if ($attachment_guids) {
		foreach ($attachment_guids as $attachment_guid) {
			add_entity_relationship($attachment_guid, 'attached', $wall_post->guid);
		}
	}

	// files being uploaded via $_FILES
	$uploads = UploadHandler::handle('upload_guids');
	if ($uploads) {
		foreach ($uploads as $upload) {
			if ($upload->guid) {
				$upload_guids[] = $upload->guid;
			}
		}
	}

	if (count($upload_guids)) {
		foreach ($upload_guids as $upload_guid) {
			$upload = get_entity($upload_guid);
			$upload->description = $wall_post->description;
			$upload->origin = 'wall';
			$upload->access_id = $wall_post->access_id;
			$upload->container_guid = ($container->canWriteToContainer($poster->guid, 'object', 'file')) ? $container->guid : ELGG_ENTITIES_ANY_VALUE;
			$upload->save();
			add_entity_relationship($upload_guid, 'attached', $wall_post->guid);
		}
	}

	$wall_post->setLocation($location);

	if ($fp = curl_init($address)) {
		$wall_post->address = $address;
	}

	if ($wall_post->address && get_input('make_bookmark', false)) {

		$embedder = new Embedder($wall_post->address);
		$document = $embedder->extractMeta('iframely');

		$bookmark = new ElggObject;
		$bookmark->subtype = "bookmarks";
		$bookmark->container_guid = ($container->canWriteToContainer($poster->guid, 'object', 'bookmarks')) ? $container->guid : ELGG_ENTITIES_ANY_VALUE;
		$bookmark->address = $wall_post->address;
		$bookmark->access_id = $access_id;
		$bookmark->origin = 'wall';

		if (!$document) {
			$bookmark->title = $wall_post->title;
			$bookmark->description = $wall_post->description;
			$bookmark->tags = $wall_post->tags;
		} else {
			$bookmark->title = filter_tags($document->meta->title);
			$bookmark->description = filter_tags($document->meta->description);
			$bookmark->tags = string_to_tag_array(filter_tags($document->meta->keywords));
		}

		$bookmark->save();
	}

	if ($wall_post->save()) {
		$message = format_wall_message($wall_post);
		$params = array(
			'entity' => $wall_post,
			'user' => $poster,
			'message' => $message,
			'url' => $wall_post->getURL(),
			'origin' => 'wall',
		);
		elgg_trigger_plugin_hook('status', 'user', $params);

		// Trigger a publish event, so that we can send out notifications
		elgg_trigger_event('publish', 'object', $wall_post);

		if (get_input('widget')) {
			elgg_push_context('widgets');
		}

		if (elgg_is_xhr()) {
			if (get_input('river') && get_input('river') != 'false') {
				echo elgg_list_river(array('object_guids' => $wall_post->guid));
			} else {
				elgg_set_page_owner_guid($wall_owner->guid);
				echo elgg_view_entity($wall_post, array('full_view' => false));
			}
		}
		
		system_message(elgg_echo('wall:create:success'));
		forward($wall_post->getURL());
	}
}

register_error(elgg_echo('wall:create:error'));
forward(REFERER);

